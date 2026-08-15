<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Enums\Accounting\JournalStatus;
use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\FinancialStatement;
use App\Models\JournalEntryLine;
use App\Models\User;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\DB;

final class StatementBuilderService
{
    /**
     * Build and persist a financial statement from posted journal lines.
     *
     * @param  'income_statement'|'balance_sheet'|'cash_flow'|'equity_changes'  $type
     */
    public function build(Company $company, AccountingPeriod $period, string $type, User $generatedBy): FinancialStatement
    {
        $implemented = ['income_statement', 'balance_sheet', 'cash_flow', 'equity_changes'];
        if (! in_array($type, $implemented, true)) {
            throw new \DomainException(
                "Financial statement type [{$type}] is not supported. Supported: ".implode(', ', $implemented),
            );
        }

        return DB::transaction(function () use ($company, $period, $type, $generatedBy): FinancialStatement {
            $data = match ($type) {
                'income_statement' => $this->buildIncomeStatement($company, $period),
                'balance_sheet' => $this->buildBalanceSheet($company, $period),
                'cash_flow' => $this->buildCashFlow($company, $period),
                'equity_changes' => $this->buildEquityChanges($company, $period),
            };

            /** @var FinancialStatement $statement */
            $statement = FinancialStatement::create([
                'company_id' => $company->id,
                'accounting_period_id' => $period->id,
                'statement_type' => $type,
                'status' => 'draft',
                'version' => 1,
                'is_locked' => false,
                'data' => $data,
                'generated_by' => $generatedBy->id,
            ]);

            return $statement;
        });
    }

    /** @return array<string, mixed> */
    private function buildIncomeStatement(Company $company, AccountingPeriod $period): array
    {
        $currency = $company->currency;
        $balances = $this->aggregatePostedBalances($company, $period);

        $revenue = Money::zero($currency);
        $cogs = Money::zero($currency);
        $expense = Money::zero($currency);
        $otherInc = Money::zero($currency);
        $otherExp = Money::zero($currency);

        $revenueLines = [];
        $cogsLines = [];
        $expenseLines = [];
        $otherIncLines = [];
        $otherExpLines = [];

        foreach ($balances as $accountId => $data) {
            $net = new Money($data['net'], $currency);
            $acctType = $data['account_type'];
            $line = ['account_id' => $accountId, 'account_code' => $data['account_code'], 'account_name' => $data['account_name'], 'amount' => $data['net']];

            match ($acctType) {
                'revenue' => [$revenueLines[] = $line,  $revenue = $revenue->add(new Money(ltrim($data['net'], '-'), $currency))],
                'cost_of_goods_sold' => [$cogsLines[] = $line,     $cogs = $cogs->add($net)],
                'expense' => [$expenseLines[] = $line,   $expense = $expense->add($net)],
                'other_income' => [$otherIncLines[] = $line,  $otherInc = $otherInc->add(new Money(ltrim($data['net'], '-'), $currency))],
                'other_expense' => [$otherExpLines[] = $line,  $otherExp = $otherExp->add($net)],
                default => null,
            };
        }

        $grossProfit = $revenue->subtract($cogs);
        $netIncome = $grossProfit->subtract($expense)->add($otherInc)->subtract($otherExp);

        return [
            'revenue' => ['lines' => $revenueLines,  'total' => $revenue->getAmount()],
            'cogs' => ['lines' => $cogsLines,     'total' => $cogs->getAmount()],
            'gross_profit' => $grossProfit->getAmount(),
            'expenses' => ['lines' => $expenseLines,  'total' => $expense->getAmount()],
            'other_income' => ['lines' => $otherIncLines, 'total' => $otherInc->getAmount()],
            'other_expenses' => ['lines' => $otherExpLines, 'total' => $otherExp->getAmount()],
            'net_income' => $netIncome->getAmount(),
        ];
    }

    /** @return array<string, mixed> */
    private function buildBalanceSheet(Company $company, AccountingPeriod $period): array
    {
        $currency = $company->currency;
        $balances = $this->aggregatePostedBalances($company, $period);

        $assets = Money::zero($currency);
        $liabilities = Money::zero($currency);
        $equity = Money::zero($currency);

        $assetLines = [];
        $leqLines = [];

        foreach ($balances as $accountId => $data) {
            $net = new Money($data['net'], $currency);
            $acctType = $data['account_type'];
            $line = ['account_id' => $accountId, 'account_code' => $data['account_code'], 'account_name' => $data['account_name'], 'amount' => $data['net']];

            if ($acctType === 'asset') {
                $assetLines[] = $line;
                $assets = $assets->add($net);
            } elseif ($acctType === 'liability') {
                $leqLines[] = $line;
                $liabilities = $liabilities->add($net);
            } elseif ($acctType === 'equity') {
                $leqLines[] = $line;
                $equity = $equity->add($net);
            }
        }

        $totalLeq = $liabilities->add($equity);

        return [
            'assets' => ['lines' => $assetLines, 'total' => $assets->getAmount()],
            'liabilities_and_equity' => ['lines' => $leqLines,   'total' => $totalLeq->getAmount()],
            'is_balanced' => $assets->equals($totalLeq),
        ];
    }

    /**
     * Build a direct cash movement statement from posted cash and bank accounts.
     * Classification is intentionally limited to accounts explicitly named
     * Cash or Bank; unsupported classification must never invent a value.
     *
     * @return array<string, mixed>
     */
    private function buildCashFlow(Company $company, AccountingPeriod $period): array
    {
        $currency = $company->currency;
        $balances = $this->aggregatePostedBalances($company, $period);
        $cashLines = [];
        $netChange = Money::zero($currency);

        foreach ($balances as $accountId => $data) {
            $accountName = strtolower($data['account_name']);
            if ($data['account_type'] !== 'asset'
                || (! str_contains($accountName, 'cash') && ! str_contains($accountName, 'bank'))) {
                continue;
            }

            $amount = new Money($data['net'], $currency);
            $cashLines[] = [
                'account_id' => $accountId,
                'account_code' => $data['account_code'],
                'account_name' => $data['account_name'],
                'amount' => $amount->getAmount(),
            ];
            $netChange = $netChange->add($amount);
        }

        return [
            'operating_activities' => ['lines' => $cashLines, 'total' => $netChange->getAmount()],
            'investing_activities' => ['lines' => [], 'total' => '0.00'],
            'financing_activities' => ['lines' => [], 'total' => '0.00'],
            'net_change' => $netChange->getAmount(),
        ];
    }

    /**
     * Build an equity movement statement from posted equity account balances.
     *
     * @return array<string, mixed>
     */
    private function buildEquityChanges(Company $company, AccountingPeriod $period): array
    {
        $currency = $company->currency;
        $balances = $this->aggregatePostedBalances($company, $period);
        $equityLines = [];
        $total = Money::zero($currency);

        foreach ($balances as $accountId => $data) {
            if ($data['account_type'] !== 'equity') {
                continue;
            }

            $amount = new Money($data['net'], $currency);
            $equityLines[] = [
                'account_id' => $accountId,
                'account_code' => $data['account_code'],
                'account_name' => $data['account_name'],
                'amount' => $amount->getAmount(),
            ];
            $total = $total->add($amount);
        }

        return [
            'equity' => ['lines' => $equityLines, 'total' => $total->getAmount()],
            'net_change' => $total->getAmount(),
        ];
    }

    /**
     * Aggregate net balance per account from posted journal lines.
     * net = debit - credit (positive = debit-heavy, negative = credit-heavy)
     *
     * @return array<int, array{net: string, account_code: string, account_name: string, account_type: string}>
     */
    private function aggregatePostedBalances(Company $company, AccountingPeriod $period): array
    {
        $rows = JournalEntryLine::query()
            ->join('journal_entries as je', 'je.id', '=', 'journal_entry_lines.journal_entry_id')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'journal_entry_lines.account_id')
            ->where('je.company_id', $company->id)
            ->where('je.accounting_period_id', $period->id)
            ->where('je.status', JournalStatus::Posted->value)
            ->selectRaw('
                journal_entry_lines.account_id,
                coa.account_code,
                coa.account_name,
                coa.account_type,
                SUM(journal_entry_lines.debit)  AS total_debit,
                SUM(journal_entry_lines.credit) AS total_credit
            ')
            ->groupBy('journal_entry_lines.account_id', 'coa.account_code', 'coa.account_name', 'coa.account_type')
            ->get();

        $result = [];

        foreach ($rows as $row) {
            $net = bcsub(
                bcadd((string) $row->total_debit, '0', 2),
                bcadd((string) $row->total_credit, '0', 2),
                2,
            );

            $result[$row->account_id] = [
                'net' => $net,
                'account_code' => $row->account_code,
                'account_name' => $row->account_name,
                'account_type' => $row->account_type,
            ];
        }

        return $result;
    }
}
