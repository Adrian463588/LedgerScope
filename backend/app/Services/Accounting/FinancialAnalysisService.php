<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Enums\Accounting\AccountType;
use App\Models\Company;
use App\Models\TrialBalance;
use App\Models\TrialBalanceLine;
use App\Support\Decimal;

final class FinancialAnalysisService
{
    /**
     * Calculate ratios for the latest trial balance of a company.
     *
     * @return array<string, mixed>
     */
    public function calculateRatios(Company $company, ?int $periodId = null, array $filters = []): array
    {
        $query = TrialBalance::where('company_id', $company->id);

        if ($periodId !== null) {
            $query->where('accounting_period_id', $periodId);
        }

        /** @var TrialBalance|null $trialBalance */
        $trialBalance = $query->latest('generated_at')->first();

        if ($trialBalance === null) {
            return $this->emptyRatios();
        }

        $totals = $this->collectTotals($trialBalance);
        $netIncome = Decimal::subtract(
            Decimal::subtract($totals['revenue'], $totals['cogs']),
            $totals['expenses'],
        );

        $currentRatio = Decimal::divide($totals['current_assets'], $totals['current_liabilities']);
        $quickRatio = Decimal::divide($totals['quick_assets'], $totals['current_liabilities']);
        $debtToEquity = Decimal::divide($totals['total_liabilities'], $totals['total_equity']);
        $grossProfitMargin = Decimal::percentage(
            Decimal::subtract($totals['revenue'], $totals['cogs']),
            $totals['revenue'],
        );
        $netProfitMargin = Decimal::percentage($netIncome, $totals['revenue']);
        $roa = Decimal::percentage($netIncome, $totals['total_assets']);
        $roe = Decimal::percentage($netIncome, $totals['total_equity']);

        return [
            'current_ratio' => $this->formatRatio($currentRatio),
            'quick_ratio' => $this->formatRatio($quickRatio),
            'debt_to_equity' => $this->formatRatio($debtToEquity),
            'gross_profit_margin' => $this->formatPercentage($grossProfitMargin),
            'net_profit_margin' => $this->formatPercentage($netProfitMargin),
            'roa' => $this->formatPercentage($roa),
            'roe' => $this->formatPercentage($roe),
            'raw' => [
                ...$totals,
                'net_income' => $netIncome,
            ],
        ];
    }

    /**
     * Calculate up to eight trial-balance periods without converting amounts
     * to floating point values.
     *
     * @return array<string, array<int, string>|array<int, string>>
     */
    public function getTrends(Company $company, array $filters = []): array
    {
        $trialBalances = TrialBalance::where('company_id', $company->id)
            ->with(['period', 'lines.account'])
            ->orderBy('generated_at')
            ->take(8)
            ->get();

        $labels = [];
        $revenues = [];
        $expenses = [];
        $netIncomes = [];

        foreach ($trialBalances as $trialBalance) {
            $labels[] = $trialBalance->period?->period_name
                ?? $trialBalance->generated_at->format('Y-M');

            $revenue = '0.00';
            $expense = '0.00';
            $cogs = '0.00';

            foreach ($trialBalance->lines as $line) {
                if ($line->account === null) {
                    continue;
                }

                $balance = $this->closingBalance($line, $line->account->account_type);

                if (in_array($line->account->account_type, [AccountType::Revenue, AccountType::OtherIncome], true)) {
                    $revenue = Decimal::add($revenue, $balance);
                } elseif (in_array($line->account->account_type, [AccountType::Expense, AccountType::OtherExpense], true)) {
                    $expense = Decimal::add($expense, $balance);
                } elseif ($line->account->account_type === AccountType::Cogs) {
                    $cogs = Decimal::add($cogs, $balance);
                }
            }

            $revenues[] = $revenue;
            $expenses[] = Decimal::add($expense, $cogs);
            $netIncomes[] = Decimal::subtract(Decimal::subtract($revenue, $expense), $cogs);
        }

        return [
            'labels' => $labels,
            'revenues' => $revenues,
            'expenses' => $expenses,
            'net_incomes' => $netIncomes,
        ];
    }

    /**
     * Calculate variance between two periods.
     *
     * @return array<string, mixed>
     */
    public function calculateVariance(Company $company, ?int $periodId = null, ?int $comparePeriodId = null, array $filters = []): array
    {
        $query = TrialBalance::where('company_id', $company->id);

        if ($periodId !== null) {
            $query->where('accounting_period_id', $periodId);
        }

        /** @var TrialBalance|null $current */
        $current = $query->latest('generated_at')->first();

        if ($current === null) {
            return [
                'period' => 'N/A',
                'compare_period' => 'N/A',
                'variances' => [],
            ];
        }

        $compareQuery = TrialBalance::where('company_id', $company->id);

        if ($comparePeriodId !== null) {
            $compareQuery->where('accounting_period_id', $comparePeriodId);
        } else {
            $compareQuery->where('id', '!=', $current->id)
                ->where('generated_at', '<', $current->generated_at);
        }

        /** @var TrialBalance|null $compare */
        $compare = $compareQuery->latest('generated_at')->first();
        $currentBalances = $this->getBalancesByCategory($current, $filters);
        $compareBalances = $compare === null ? [] : $this->getBalancesByCategory($compare, $filters);
        $variances = [];

        foreach (['Revenue', 'COGS', 'Expense', 'Asset', 'Liability', 'Equity'] as $category) {
            $currentValue = $currentBalances[$category] ?? '0.00';
            $compareValue = $compareBalances[$category] ?? '0.00';
            $difference = Decimal::subtract($currentValue, $compareValue);
            $percentage = Decimal::percentage($difference, $compareValue);

            $variances[] = [
                'category' => $category,
                'current' => Decimal::format($currentValue),
                'compare' => Decimal::format($compareValue),
                'variance' => Decimal::format($difference),
                'percentage' => $this->formatPercentage($percentage),
            ];
        }

        return [
            'period' => $this->periodName($current),
            'compare_period' => $compare === null ? 'N/A' : $this->periodName($compare),
            'variances' => $variances,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function collectTotals(TrialBalance $trialBalance): array
    {
        $totals = [
            'current_assets' => '0.00',
            'current_liabilities' => '0.00',
            'quick_assets' => '0.00',
            'total_assets' => '0.00',
            'total_liabilities' => '0.00',
            'total_equity' => '0.00',
            'revenue' => '0.00',
            'cogs' => '0.00',
            'expenses' => '0.00',
        ];

        $lines = TrialBalanceLine::where('trial_balance_id', $trialBalance->id)
            ->with('account')
            ->get();

        foreach ($lines as $line) {
            if ($line->account === null) {
                continue;
            }

            $type = $line->account->account_type;
            $balance = $this->closingBalance($line, $type);

            switch ($type) {
                case AccountType::Asset:
                    $totals['total_assets'] = Decimal::add($totals['total_assets'], $balance);

                    if ($line->account->account_code < '1500') {
                        $totals['current_assets'] = Decimal::add($totals['current_assets'], $balance);

                        if (! str_starts_with($line->account->account_code, '13')
                            && stripos($line->account->account_name, 'inventory') === false) {
                            $totals['quick_assets'] = Decimal::add($totals['quick_assets'], $balance);
                        }
                    }
                    break;
                case AccountType::Liability:
                    $totals['total_liabilities'] = Decimal::add($totals['total_liabilities'], $balance);

                    if ($line->account->account_code < '2500') {
                        $totals['current_liabilities'] = Decimal::add($totals['current_liabilities'], $balance);
                    }
                    break;
                case AccountType::Equity:
                    $totals['total_equity'] = Decimal::add($totals['total_equity'], $balance);
                    break;
                case AccountType::Revenue:
                case AccountType::OtherIncome:
                    $totals['revenue'] = Decimal::add($totals['revenue'], $balance);
                    break;
                case AccountType::Cogs:
                    $totals['cogs'] = Decimal::add($totals['cogs'], $balance);
                    break;
                case AccountType::Expense:
                case AccountType::OtherExpense:
                    $totals['expenses'] = Decimal::add($totals['expenses'], $balance);
                    break;
            }
        }

        return $totals;
    }

    /**
     * @return array<string, string>
     */
    private function getBalancesByCategory(TrialBalance $trialBalance, array $filters): array
    {
        $balances = array_fill_keys(['Revenue', 'COGS', 'Expense', 'Asset', 'Liability', 'Equity'], '0.00');
        $lines = TrialBalanceLine::where('trial_balance_id', $trialBalance->id)->with('account')->get();

        foreach ($lines as $line) {
            if ($line->account === null) {
                continue;
            }

            $type = $line->account->account_type;

            if (! $type instanceof AccountType) {
                continue;
            }

            if (! empty($filters['account_category']) && stripos($type->value, (string) $filters['account_category']) === false) {
                continue;
            }

            $category = match ($type) {
                AccountType::Asset => 'Asset',
                AccountType::Liability => 'Liability',
                AccountType::Equity => 'Equity',
                AccountType::Revenue, AccountType::OtherIncome => 'Revenue',
                AccountType::Cogs => 'COGS',
                AccountType::Expense, AccountType::OtherExpense => 'Expense',
            };

            $balances[$category] = Decimal::add(
                $balances[$category],
                $this->closingBalance($line, $type),
            );
        }

        return $balances;
    }

    /**
     * @return array<string, string>
     */
    private function emptyRatios(): array
    {
        return [
            'current_ratio' => 'N/A',
            'net_profit_margin' => 'N/A',
            'debt_to_equity' => 'N/A',
            'quick_ratio' => 'N/A',
            'roa' => 'N/A',
            'roe' => 'N/A',
            'gross_profit_margin' => 'N/A',
        ];
    }

    private function closingBalance(TrialBalanceLine $line, AccountType $type): string
    {
        return $type->isCreditNormal()
            ? Decimal::subtract((string) $line->closing_credit, (string) $line->closing_debit)
            : Decimal::subtract((string) $line->closing_debit, (string) $line->closing_credit);
    }

    private function formatRatio(?string $value): string
    {
        return $value === null ? 'N/A' : Decimal::format($value).'x';
    }

    private function formatPercentage(?string $value): string
    {
        return $value === null ? 'N/A' : Decimal::format($value, 1).'%';
    }

    private function periodName(TrialBalance $trialBalance): string
    {
        return $trialBalance->period?->period_name
            ?? $trialBalance->generated_at->format('Y-M');
    }
}
