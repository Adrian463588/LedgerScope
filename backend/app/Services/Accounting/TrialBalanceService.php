<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Enums\Accounting\JournalStatus;
use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\JournalEntryLine;
use App\Models\TrialBalance;
use App\Models\TrialBalanceLine;
use App\Models\User;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\DB;

final class TrialBalanceService
{
    /**
     * Generate a trial balance snapshot for the given period.
     * Only POSTED journal entry lines are included.
     * Wrapped in DB::transaction() as per absolute rule §10.
     */
    public function generate(Company $company, AccountingPeriod $period, User $generatedBy): TrialBalance
    {
        return DB::transaction(function () use ($company, $period, $generatedBy): TrialBalance {
            // Aggregate posted lines by account for this period
            $lines = JournalEntryLine::query()
                ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
                ->where('journal_entries.company_id', $company->id)
                ->where('journal_entries.accounting_period_id', $period->id)
                ->where('journal_entries.status', JournalStatus::Posted->value)
                ->selectRaw('
                    journal_entry_lines.account_id,
                    SUM(journal_entry_lines.debit)  AS total_debit,
                    SUM(journal_entry_lines.credit) AS total_credit
                ')
                ->groupBy('journal_entry_lines.account_id')
                ->get();

            $currency = $company->currency;
            $totalDebit = Money::zero($currency);
            $totalCredit = Money::zero($currency);

            foreach ($lines as $line) {
                $totalDebit = $totalDebit->add(new Money((string) $line->total_debit, $currency));
                $totalCredit = $totalCredit->add(new Money((string) $line->total_credit, $currency));
            }

            $isBalanced = $totalDebit->equals($totalCredit);

            /** @var TrialBalance $trialBalance */
            $trialBalance = TrialBalance::create([
                'company_id' => $company->id,
                'accounting_period_id' => $period->id,
                'total_debit' => $totalDebit->getAmount(),
                'total_credit' => $totalCredit->getAmount(),
                'is_balanced' => $isBalanced,
                'status' => $isBalanced ? 'balanced' : 'unbalanced',
                'generated_at' => now(),
                'generated_by' => $generatedBy->id,
            ]);

            // Insert per-account lines
            foreach ($lines as $line) {
                $debit = bcadd((string) $line->total_debit, '0', 2);
                $credit = bcadd((string) $line->total_credit, '0', 2);

                $closing = bcsub($debit, $credit, 2);
                $closingDebit = bccomp($closing, '0', 2) >= 0 ? $closing : '0.00';
                $closingCredit = bccomp($closing, '0', 2) < 0 ? ltrim($closing, '-') : '0.00';

                TrialBalanceLine::create([
                    'trial_balance_id' => $trialBalance->id,
                    'account_id' => $line->account_id,
                    'opening_debit' => '0.00',
                    'opening_credit' => '0.00',
                    'period_debit' => $debit,
                    'period_credit' => $credit,
                    'closing_debit' => $closingDebit,
                    'closing_credit' => $closingCredit,
                ]);
            }

            return $trialBalance->load('lines');
        });
    }
}
