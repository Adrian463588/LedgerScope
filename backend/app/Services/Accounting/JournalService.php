<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Enums\Accounting\JournalSourceType;
use App\Enums\Accounting\JournalStatus;
use App\Events\Accounting\JournalApproved;
use App\Events\Accounting\JournalCreated;
use App\Events\Accounting\JournalPosted;
use App\Events\Accounting\JournalRejected;
use App\Events\Accounting\JournalReversed;
use App\Events\Accounting\JournalSubmitted;
use App\Models\AccountingPeriod;
use App\Models\JournalEntry;
use App\Models\User;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\DB;

/**
 * JournalService — core double-entry accounting engine.
 *
 * Rules enforced (AGENTS.md Phase 5):
 * - Period must be open + not locked before creating/posting
 * - Post: debit total must equal credit total (via Money VO)
 * - Post: minimum 2 lines required
 * - Post: all accounts must be active
 * - Posted journals are immutable (JournalEntry model enforces this)
 * - Reversal creates new negated journal, links reversed_from_id
 */
final class JournalService
{
    /**
     * @param  array{
     *     accounting_period_id: int,
     *     description: string,
     *     journal_date: string,
     *     reference?: string,
     *     source_type?: string,
     *     lines: array<int, array{account_id: int, description?: string, debit: string, credit: string, currency?: string}>
     * }  $dto
     */
    public function create(array $dto, User $user): JournalEntry
    {
        $period = AccountingPeriod::findOrFail($dto['accounting_period_id']);

        if (! $period->isOpen()) {
            throw new \DomainException('Cannot create a journal entry in a locked or closed period.');
        }

        return DB::transaction(function () use ($dto, $user, $period): JournalEntry {
            /** @var JournalEntry $journal */
            $journal = JournalEntry::create([
                'company_id' => $period->company_id,
                'accounting_period_id' => $period->id,
                'description' => $dto['description'],
                'journal_date' => $dto['journal_date'],
                'reference' => $dto['reference'] ?? null,
                'source_type' => $dto['source_type'] ?? JournalSourceType::Manual->value,
                'status' => JournalStatus::Draft->value,
                'created_by' => $user->id,
            ]);

            foreach ($dto['lines'] as $line) {
                $journal->lines()->create([
                    'account_id' => $line['account_id'],
                    'description' => $line['description'] ?? null,
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                    'currency' => $line['currency'] ?? $period->company->currency ?? 'IDR',
                ]);
            }

            event(new JournalCreated(
                userId: $user->id,
                action: 'create_journal',
                companyId: $period->company_id,
                objectType: 'JournalEntry',
                objectId: $journal->id,
            ));

            return $journal->load('lines');
        });
    }

    public function submit(JournalEntry $journal, User $user): void
    {
        if ($journal->status !== JournalStatus::Draft) {
            throw new \DomainException("Journal must be in Draft status to submit. Current: {$journal->status->value}");
        }

        DB::transaction(function () use ($journal, $user): void {
            $journal->timestamps = false;
            $journal->forceFill([
                'status' => JournalStatus::Submitted->value,
                'submitted_by' => $user->id,
            ])->save();

            event(new JournalSubmitted(
                userId: $user->id,
                action: 'submit_journal',
                companyId: $journal->company_id,
                objectType: 'JournalEntry',
                objectId: $journal->id,
            ));
        });
    }

    public function approve(JournalEntry $journal, User $user): void
    {
        if (! in_array($journal->status, [JournalStatus::Submitted, JournalStatus::Reviewed], true)) {
            throw new \DomainException('Journal must be Submitted or Reviewed to approve.');
        }

        DB::transaction(function () use ($journal, $user): void {
            $journal->forceFill([
                'status' => JournalStatus::Approved->value,
                'approved_by' => $user->id,
            ])->save();

            event(new JournalApproved(
                userId: $user->id,
                action: 'approve_journal',
                companyId: $journal->company_id,
                objectType: 'JournalEntry',
                objectId: $journal->id,
            ));
        });
    }

    public function post(JournalEntry $journal, User $user): void
    {
        if ($journal->status !== JournalStatus::Approved) {
            throw new \DomainException('Journal must be Approved to post.');
        }

        $period = $journal->accountingPeriod;

        if ($period->isLocked()) {
            throw new \DomainException('Cannot post to a locked period.');
        }

        if (! $period->isOpen()) {
            throw new \DomainException('Cannot post to a locked or closed period.');
        }

        $lines = $journal->lines;

        if ($lines->count() < 2) {
            throw new \DomainException('Journal entry must have at least 2 lines.');
        }

        // Validate all accounts are active
        foreach ($lines as $line) {
            if (! $line->account->is_active) {
                throw new \DomainException("Account [{$line->account->account_code}] is inactive.");
            }
        }

        // Validate debit == credit using Money VO (bcmath)
        $currency = $lines->first()->currency ?? 'IDR';
        $totalDebit = Money::zero($currency);
        $totalCredit = Money::zero($currency);

        foreach ($lines as $line) {
            $totalDebit = $totalDebit->add(new Money($line->debit, $currency));
            $totalCredit = $totalCredit->add(new Money($line->credit, $currency));
        }

        if (! $totalDebit->equals($totalCredit)) {
            throw new \DomainException(
                "Journal does not balance. Debit: {$totalDebit} | Credit: {$totalCredit}",
            );
        }

        // Validate journal date is within period
        if ($journal->journal_date->lt($period->start_date) || $journal->journal_date->gt($period->end_date)) {
            throw new \DomainException('Journal date is outside the accounting period range.');
        }

        DB::transaction(function () use ($journal, $user): void {
            $journalNumber = $this->generateJournalNumber($journal->company_id);

            $journal->forceFill([
                'status' => JournalStatus::Posted->value,
                'journal_number' => $journalNumber,
                'posted_by' => $user->id,
                'posted_at' => now(),
            ])->save();

            event(new JournalPosted(
                userId: $user->id,
                action: 'post_journal',
                companyId: $journal->company_id,
                objectType: 'JournalEntry',
                objectId: $journal->id,
            ));
        });
    }

    public function reverse(JournalEntry $journal, User $user, string $reason): JournalEntry
    {
        if ($journal->status !== JournalStatus::Posted) {
            throw new \DomainException('Only posted journals can be reversed.');
        }

        return DB::transaction(function () use ($journal, $user, $reason): JournalEntry {
            $period = $journal->accountingPeriod;

            // B-03 fix: use today if within period, otherwise use period end_date
            $today = now()->toDateString();
            $reversalDate = ($today >= $period->start_date->toDateString() && $today <= $period->end_date->toDateString())
                ? $today
                : $period->end_date->toDateString();

            // Create negated journal
            /** @var JournalEntry $reversal */
            $reversal = JournalEntry::create([
                'company_id' => $journal->company_id,
                'accounting_period_id' => $journal->accounting_period_id,
                'description' => "REVERSAL: {$journal->description} — {$reason}",
                'journal_date' => $reversalDate,
                'reference' => $journal->reference,
                'source_type' => JournalSourceType::Reversal->value,
                'status' => JournalStatus::Approved->value,
                'reversed_from_id' => $journal->id,
                'created_by' => $user->id,
                'approved_by' => $user->id,
            ]);

            // Negate each line (swap debit/credit)
            foreach ($journal->lines as $line) {
                $reversal->lines()->create([
                    'account_id' => $line->account_id,
                    'description' => $line->description,
                    'debit' => $line->credit,   // swapped
                    'credit' => $line->debit,    // swapped
                    'currency' => $line->currency,
                ]);
            }

            // Post immediately
            $this->post($reversal, $user);

            // Mark original as reversed
            $journal->forceFill(['status' => JournalStatus::Reversed->value])->save();

            event(new JournalReversed(
                userId: $user->id,
                action: 'reverse_journal',
                companyId: $journal->company_id,
                objectType: 'JournalEntry',
                objectId: $journal->id,
                metadata: ['reversal_id' => $reversal->id, 'reason' => $reason],
            ));

            return $reversal->load('lines');
        });
    }

    public function reject(JournalEntry $journal, User $user, string $reason): void
    {
        if (! in_array($journal->status, [JournalStatus::Submitted, JournalStatus::Reviewed, JournalStatus::Approved], true)) {
            throw new \DomainException('Journal cannot be rejected from its current status.');
        }

        DB::transaction(function () use ($journal, $user, $reason): void {
            $journal->forceFill(['status' => JournalStatus::Rejected->value])->save();

            event(new JournalRejected(
                userId: $user->id,
                action: 'reject_journal',
                companyId: $journal->company_id,
                objectType: 'JournalEntry',
                objectId: $journal->id,
                metadata: ['reason' => $reason],
            ));
        });
    }

    /**
     * Generate sequential journal number per company: JNL-{COMPANY_ID}-{YEAR}-{SEQUENCE}.
     */
    private function generateJournalNumber(int $companyId): string
    {
        $year = now()->year;
        $count = JournalEntry::where('company_id', $companyId)
            ->whereYear('posted_at', $year)
            ->whereNotNull('journal_number')
            ->count();

        return sprintf('JNL-%d-%d-%05d', $companyId, $year, $count + 1);
    }
}
