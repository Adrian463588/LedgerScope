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
            // Get opening balances from the latest closed/locked period before this one
            $previousPeriod = AccountingPeriod::where('company_id', $company->id)
                ->where('end_date', '<', $period->start_date)
                ->orderByDesc('end_date')
                ->first();

            $openingBalances = collect();
            if ($previousPeriod) {
                $previousTb = TrialBalance::with('lines')
                    ->where('company_id', $company->id)
                    ->where('accounting_period_id', $previousPeriod->id)
                    ->latest('generated_at')
                    ->first();

                if ($previousTb) {
                    $openingBalances = $previousTb->lines->keyBy('account_id');
                }
            }

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
                ->get()
                ->keyBy('account_id');

            $allAccountIds = $openingBalances->keys()->merge($lines->keys())->unique();

            $currency = $company->currency;
            $totalDebit = Money::zero($currency);
            $totalCredit = Money::zero($currency);

            $tbLinesData = [];

            foreach ($allAccountIds as $accountId) {
                $openingLine = $openingBalances->get($accountId);
                $movementLine = $lines->get($accountId);

                $openingDebit = $openingLine ? new Money($openingLine->closing_debit, $currency) : Money::zero($currency);
                $openingCredit = $openingLine ? new Money($openingLine->closing_credit, $currency) : Money::zero($currency);

                $periodDebit = $movementLine ? new Money((string) $movementLine->total_debit, $currency) : Money::zero($currency);
                $periodCredit = $movementLine ? new Money((string) $movementLine->total_credit, $currency) : Money::zero($currency);

                // Calculate ending balances using Money VO
                $totalDebitSide = $openingDebit->add($periodDebit);
                $totalCreditSide = $openingCredit->add($periodCredit);

                if ($totalDebitSide->greaterThan($totalCreditSide) || $totalDebitSide->equals($totalCreditSide)) {
                    $closingDebit = $totalDebitSide->subtract($totalCreditSide);
                    $closingCredit = Money::zero($currency);
                } else {
                    $closingDebit = Money::zero($currency);
                    $closingCredit = $totalCreditSide->subtract($totalDebitSide);
                }

                $totalDebit = $totalDebit->add($closingDebit);
                $totalCredit = $totalCredit->add($closingCredit);

                $tbLinesData[] = [
                    'account_id' => $accountId,
                    'opening_debit' => $openingDebit->getAmount(),
                    'opening_credit' => $openingCredit->getAmount(),
                    'period_debit' => $periodDebit->getAmount(),
                    'period_credit' => $periodCredit->getAmount(),
                    'closing_debit' => $closingDebit->getAmount(),
                    'closing_credit' => $closingCredit->getAmount(),
                ];
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
            foreach ($tbLinesData as $lineData) {
                $lineData['trial_balance_id'] = $trialBalance->id;
                TrialBalanceLine::create($lineData);
            }

            return $trialBalance->load('lines');
        });
    }
}
