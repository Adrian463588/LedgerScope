<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Events\AuditActionRecorded;
use App\Models\Company;
use App\Models\JournalEntryLine;
use App\Models\Reconciliation;
use App\Models\ReconciliationItem;
use App\Models\User;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\DB;

final class ReconciliationService
{
    /**
     * Create a new reconciliation record.
     * Computes difference = book_balance - bank_balance using bcmath.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, Company $company, User $by): Reconciliation
    {
        if (! $company->accounts()->whereKey($data['account_id'])->exists()
            || ! $company->accountingPeriods()->whereKey($data['accounting_period_id'])->exists()) {
            throw new \DomainException('Accounting resource does not belong to this company.');
        }

        return DB::transaction(function () use ($data, $company, $by): Reconciliation {
            $currency = $company->currency;
            $bookBalance = new Money((string) $data['book_balance'], $currency);
            $bankBalance = new Money((string) $data['bank_balance'], $currency);
            $difference = $bookBalance->subtract($bankBalance);

            // Store absolute difference (sign preserved for audit)
            $diffAmount = $difference->getAmount();

            /** @var Reconciliation $rec */
            $rec = Reconciliation::create([
                'company_id' => $company->id,
                'account_id' => $data['account_id'],
                'accounting_period_id' => $data['accounting_period_id'],
                'reconciliation_type' => $data['reconciliation_type'],
                'status' => 'draft',
                'book_balance' => $bookBalance->getAmount(),
                'bank_balance' => $bankBalance->getAmount(),
                'difference' => $diffAmount,
            ]);

            event(new AuditActionRecorded(
                userId: $by->id,
                action: 'reconciliation.create',
                companyId: $company->id,
                objectType: 'Reconciliation',
                objectId: $rec->id,
                after: $rec->toArray(),
            ));

            return $rec;
        });
    }

    /**
     * Approve a draft reconciliation.
     */
    public function approve(Reconciliation $rec, User $approver): void
    {
        // B-09: Use strict string constant rather than bare magic string
        DB::transaction(function () use ($rec, $approver): void {
            /** @var Reconciliation $lockedRec */
            $lockedRec = Reconciliation::query()->lockForUpdate()->findOrFail($rec->id);

            if ($lockedRec->status !== 'draft') {
                throw new \DomainException('Only draft reconciliations can be approved.');
            }

            $lockedRec->update([
                'status' => 'approved',
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            event(new AuditActionRecorded(
                userId: $approver->id,
                action: 'reconciliation.approve',
                companyId: $lockedRec->company_id,
                objectType: 'Reconciliation',
                objectId: $lockedRec->id,
                after: $lockedRec->fresh()->toArray(),
            ));
        });
    }

    /**
     * Lock a reconciliation after approval.
     */
    public function lock(Reconciliation $rec, User $by): void
    {
        // B-09: Must be approved before locking
        DB::transaction(function () use ($rec, $by): void {
            /** @var Reconciliation $lockedRec */
            $lockedRec = Reconciliation::query()->lockForUpdate()->findOrFail($rec->id);

            if ($lockedRec->status !== 'approved') {
                throw new \DomainException('Reconciliation must be approved before locking.');
            }

            $lockedRec->update(['status' => 'locked', 'locked_by' => $by->id, 'locked_at' => now()]);

            event(new AuditActionRecorded(
                userId: $by->id,
                action: 'reconciliation.lock',
                companyId: $lockedRec->company_id,
                objectType: 'Reconciliation',
                objectId: $lockedRec->id,
                after: $lockedRec->fresh()->toArray(),
            ));
        });
    }

    /**
     * Match imported reconciliation items to posted journal lines by exact
     * account, period, date, and decimal amount.
     */
    public function autoMatch(Reconciliation $rec, User $by): Reconciliation
    {
        return DB::transaction(function () use ($rec, $by): Reconciliation {
            /** @var Reconciliation $lockedRec */
            $lockedRec = Reconciliation::query()->lockForUpdate()->findOrFail($rec->id);
            $this->assertEditable($lockedRec);

            $items = $lockedRec->items()->where('is_matched', false)->whereNull('journal_line_id')->get();
            $lines = JournalEntryLine::query()
                ->where('account_id', $lockedRec->account_id)
                ->whereHas('journalEntry', function ($query) use ($lockedRec): void {
                    $query->where('company_id', $lockedRec->company_id)
                        ->where('accounting_period_id', $lockedRec->accounting_period_id)
                        ->where('status', 'posted');
                })
                ->with('journalEntry')
                ->get();

            foreach ($items as $item) {
                $line = $lines->first(function (JournalEntryLine $candidate) use ($item): bool {
                    $amount = bccomp((string) $candidate->debit, '0.00', 2) > 0
                        ? (string) $candidate->debit
                        : (string) $candidate->credit;

                    return bccomp($amount, (string) $item->amount, 2) === 0
                        && $candidate->journalEntry?->journal_date?->toDateString() === $item->transaction_date?->toDateString();
                });

                if ($line === null) {
                    continue;
                }

                $item->update(['journal_line_id' => $line->id, 'is_matched' => true]);
                $lines = $lines->reject(static fn (JournalEntryLine $candidate): bool => $candidate->id === $line->id);
            }

            event(new AuditActionRecorded(
                userId: $by->id,
                action: 'reconciliation.auto_match',
                companyId: $lockedRec->company_id,
                objectType: 'Reconciliation',
                objectId: $lockedRec->id,
                after: ['matched_items' => $lockedRec->items()->where('is_matched', true)->count()],
            ));

            return $lockedRec->fresh(['items']);
        });
    }

    public function match(Reconciliation $rec, int $itemId, int $journalLineId, User $by): ReconciliationItem
    {
        return DB::transaction(function () use ($rec, $itemId, $journalLineId, $by): ReconciliationItem {
            /** @var Reconciliation $lockedRec */
            $lockedRec = Reconciliation::query()->lockForUpdate()->findOrFail($rec->id);
            $this->assertEditable($lockedRec);

            /** @var ReconciliationItem $item */
            $item = $lockedRec->items()->lockForUpdate()->findOrFail($itemId);
            $line = JournalEntryLine::query()
                ->whereKey($journalLineId)
                ->where('account_id', $lockedRec->account_id)
                ->whereHas('journalEntry', function ($query) use ($lockedRec): void {
                    $query->where('company_id', $lockedRec->company_id)
                        ->where('accounting_period_id', $lockedRec->accounting_period_id)
                        ->where('status', 'posted');
                })
                ->firstOrFail();

            $lineAmount = bccomp((string) $line->debit, '0.00', 2) > 0
                ? (string) $line->debit
                : (string) $line->credit;
            if (bccomp($lineAmount, (string) $item->amount, 2) !== 0) {
                throw new \DomainException('Reconciliation item amount does not match the journal line.');
            }

            $item->update(['journal_line_id' => $line->id, 'is_matched' => true]);

            event(new AuditActionRecorded(
                userId: $by->id,
                action: 'reconciliation.match',
                companyId: $lockedRec->company_id,
                objectType: 'ReconciliationItem',
                objectId: $item->id,
                after: $item->fresh()->toArray(),
            ));

            return $item->fresh();
        });
    }

    private function assertEditable(Reconciliation $rec): void
    {
        if ($rec->status === 'locked') {
            throw new \DomainException('Locked reconciliations are immutable.');
        }
    }
}
