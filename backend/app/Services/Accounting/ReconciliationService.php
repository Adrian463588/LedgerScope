<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\Company;
use App\Models\Reconciliation;
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
        return DB::transaction(function () use ($data, $company, $by): Reconciliation {
            $currency    = $company->currency;
            $bookBalance = new Money((string) $data['book_balance'], $currency);
            $bankBalance = new Money((string) $data['bank_balance'], $currency);
            $difference  = $bookBalance->subtract($bankBalance);

            // Store absolute difference (sign preserved for audit)
            $diffAmount = $difference->getAmount();

            /** @var Reconciliation $rec */
            $rec = Reconciliation::create([
                'company_id'           => $company->id,
                'account_id'           => $data['account_id'],
                'accounting_period_id' => $data['accounting_period_id'],
                'reconciliation_type'  => $data['reconciliation_type'],
                'status'               => 'draft',
                'book_balance'         => $bookBalance->getAmount(),
                'bank_balance'         => $bankBalance->getAmount(),
                'difference'           => $diffAmount,
            ]);

            return $rec;
        });
    }

    /**
     * Approve a draft reconciliation.
     */
    public function approve(Reconciliation $rec, User $approver): void
    {
        if ($rec->status !== 'draft') {
            throw new \DomainException('Only draft reconciliations can be approved.');
        }

        DB::transaction(function () use ($rec, $approver): void {
            $rec->update([
                'status'      => 'approved',
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);
        });
    }

    /**
     * Lock a reconciliation after approval.
     */
    public function lock(Reconciliation $rec, User $by): void
    {
        if ($rec->status !== 'approved') {
            throw new \DomainException('Reconciliation must be approved before locking.');
        }

        $rec->update(['status' => 'locked']);
    }
}
