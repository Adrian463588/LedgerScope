<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\Company;
use App\Models\TrialBalance;
use App\Models\TrialBalanceLine;
use App\Enums\Accounting\AccountType;

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
        $tb = $query->latest('generated_at')->first();

        if (! $tb) {
            return [
                'current_ratio' => '1.00x',
                'net_profit_margin' => '0.0%',
                'debt_to_equity' => '0.00x',
                'quick_ratio' => '1.00x',
                'roa' => '0.0%',
                'roe' => '0.0%',
                'gross_profit_margin' => '0.0%',
            ];
        }

        $currentAssets = $quickAssets = $totalAssets = 0.0;
        $currentLiabilities = $totalLiabilities = $totalEquity = 0.0;
        $revenue = $cogs = $expenses = 0.0;

        $lines = TrialBalanceLine::where('trial_balance_id', $tb->id)->with('account')->get();

        foreach ($lines as $line) {
            if (! $account = $line->account) continue;
            $type = $account->account_type;
            $code = $account->account_code;
            $balance = $type->isCreditNormal()
                ? (float) $line->closing_credit - (float) $line->closing_debit
                : (float) $line->closing_debit - (float) $line->closing_credit;

            switch ($type) {
                case AccountType::Asset:
                    $totalAssets += $balance;
                    if ($code < '1500') {
                        $currentAssets += $balance;
                        if (! str_starts_with($code, '13') && stripos($account->account_name, 'inventory') === false) {
                            $quickAssets += $balance;
                        }
                    }
                    break;
                case AccountType::Liability:
                    $totalLiabilities += $balance;
                    if ($code < '2500') $currentLiabilities += $balance;
                    break;
                case AccountType::Equity:
                    $totalEquity += $balance;
                    break;
                case AccountType::Revenue:
                case AccountType::OtherIncome:
                    $revenue += $balance;
                    break;
                case AccountType::Cogs:
                    $cogs += $balance;
                    break;
                case AccountType::Expense:
                case AccountType::OtherExpense:
                    $expenses += $balance;
                    break;
            }
        }

        $netIncome = $revenue - $cogs - $expenses;

        $currentRatioVal = $currentLiabilities > 0 ? $currentAssets / $currentLiabilities : ($currentAssets > 0 ? $currentAssets / 1.0 : 1.0);
        $quickRatioVal = $currentLiabilities > 0 ? $quickAssets / $currentLiabilities : ($quickAssets > 0 ? $quickAssets / 1.0 : 1.0);
        $debtToEquityVal = $totalEquity > 0 ? $totalLiabilities / $totalEquity : 0.0;
        $gpmVal = $revenue > 0 ? (($revenue - $cogs) / $revenue) * 100 : 0.0;
        $npmVal = $revenue > 0 ? ($netIncome / $revenue) * 100 : 0.0;
        $roaVal = $totalAssets > 0 ? ($netIncome / $totalAssets) * 100 : 0.0;
        $roeVal = $totalEquity > 0 ? ($netIncome / $totalEquity) * 100 : 0.0;

        return [
            'current_ratio' => number_format($currentRatioVal, 2) . 'x',
            'quick_ratio' => number_format($quickRatioVal, 2) . 'x',
            'debt_to_equity' => number_format($debtToEquityVal, 2) . 'x',
            'gross_profit_margin' => number_format($gpmVal, 1) . '%',
            'net_profit_margin' => number_format($npmVal, 1) . '%',
            'roa' => number_format($roaVal, 1) . '%',
            'roe' => number_format($roeVal, 1) . '%',
            'raw' => [
                'current_assets' => $currentAssets,
                'current_liabilities' => $currentLiabilities,
                'quick_assets' => $quickAssets,
                'total_assets' => $totalAssets,
                'total_liabilities' => $totalLiabilities,
                'total_equity' => $totalEquity,
                'revenue' => $revenue,
                'cogs' => $cogs,
                'expenses' => $expenses,
                'net_income' => $netIncome,
            ]
        ];
    }

    /**
     * Calculate 4-quarter or multi-period trends.
     *
     * @return array<string, mixed>
     */
    public function getTrends(Company $company, array $filters = []): array
    {
        $tbs = TrialBalance::where('company_id', $company->id)
            ->with(['period', 'lines.account'])
            ->orderBy('generated_at', 'asc')
            ->take(8)
            ->get();

        $labels = [];
        $revenues = [];
        $expenses = [];
        $netIncomes = [];

        foreach ($tbs as $tb) {
            $labels[] = $tb->period?->period_name ?? (new \Carbon\Carbon($tb->generated_at))->format('Y-M');
            $rev = $exp = $cogs = 0.0;

            foreach ($tb->lines as $line) {
                if (! $account = $line->account) continue;
                $type = $account->account_type;
                $balance = $type->isCreditNormal()
                    ? (float) $line->closing_credit - (float) $line->closing_debit
                    : (float) $line->closing_debit - (float) $line->closing_credit;

                if ($type === AccountType::Revenue || $type === AccountType::OtherIncome) {
                    $rev += $balance;
                } elseif ($type === AccountType::Expense || $type === AccountType::OtherExpense) {
                    $exp += $balance;
                } elseif ($type === AccountType::Cogs) {
                    $cogs += $balance;
                }
            }

            $revenues[] = $rev;
            $expenses[] = $exp + $cogs;
            $netIncomes[] = $rev - $exp - $cogs;
        }

        if (empty($labels)) {
            return [
                'labels' => [],
                'revenues' => [],
                'expenses' => [],
                'net_incomes' => [],
            ];
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
     */
    public function calculateVariance(Company $company, ?int $periodId = null, ?int $comparePeriodId = null, array $filters = []): array
    {
        $query = TrialBalance::where('company_id', $company->id);
        if ($periodId !== null) {
            $query->where('accounting_period_id', $periodId);
        }
        $tbCurrent = $query->latest('generated_at')->first();

        if (! $tbCurrent) {
            return [
                'period' => 'N/A',
                'compare_period' => 'N/A',
                'variances' => []
            ];
        }

        $queryCompare = TrialBalance::where('company_id', $company->id);
        if ($comparePeriodId !== null) {
            $queryCompare->where('accounting_period_id', $comparePeriodId);
        } else {
            $queryCompare->where('id', '!=', $tbCurrent->id)
                ->where('generated_at', '<', $tbCurrent->generated_at);
        }
        $tbCompare = $queryCompare->latest('generated_at')->first();

        $currentBalances = $this->getBalancesByCategory($tbCurrent, $filters);
        $compareBalances = $tbCompare ? $this->getBalancesByCategory($tbCompare, $filters) : [];

        $categories = ['Revenue', 'COGS', 'Expense', 'Asset', 'Liability', 'Equity'];
        $variances = [];

        foreach ($categories as $cat) {
            $current = $currentBalances[$cat] ?? 0.0;
            $compare = $compareBalances[$cat] ?? 0.0;
            $diff = $current - $compare;
            $pct = $compare != 0 ? ($diff / $compare) * 100 : 0.0;

            $variances[] = [
                'category' => $cat,
                'current' => number_format($current, 2, '.', ''),
                'compare' => number_format($compare, 2, '.', ''),
                'variance' => number_format($diff, 2, '.', ''),
                'percentage' => number_format($pct, 1) . '%',
            ];
        }

        return [
            'period' => $tbCurrent->period?->period_name ?? (new \Carbon\Carbon($tbCurrent->generated_at))->format('Y-M'),
            'compare_period' => $tbCompare ? ($tbCompare->period?->period_name ?? (new \Carbon\Carbon($tbCompare->generated_at))->format('Y-M')) : 'N/A',
            'variances' => $variances,
        ];
    }

    private function getBalancesByCategory(TrialBalance $tb, array $filters): array
    {
        $balances = [
            'Revenue' => 0.0,
            'COGS' => 0.0,
            'Expense' => 0.0,
            'Asset' => 0.0,
            'Liability' => 0.0,
            'Equity' => 0.0,
        ];

        $lines = TrialBalanceLine::where('trial_balance_id', $tb->id)->with('account')->get();

        foreach ($lines as $line) {
            if (! $account = $line->account) continue;
            $type = $account->account_type;
            
            // Apply account_category filter if provided
            if (! empty($filters['account_category']) && stripos($type->value, $filters['account_category']) === false) {
                continue;
            }

            $balance = $type->isCreditNormal()
                ? (float) $line->closing_credit - (float) $line->closing_debit
                : (float) $line->closing_debit - (float) $line->closing_credit;

            switch ($type) {
                case AccountType::Asset:
                    $balances['Asset'] += $balance;
                    break;
                case AccountType::Liability:
                    $balances['Liability'] += $balance;
                    break;
                case AccountType::Equity:
                    $balances['Equity'] += $balance;
                    break;
                case AccountType::Revenue:
                case AccountType::OtherIncome:
                    $balances['Revenue'] += $balance;
                    break;
                case AccountType::Cogs:
                    $balances['COGS'] += $balance;
                    break;
                case AccountType::Expense:
                case AccountType::OtherExpense:
                    $balances['Expense'] += $balance;
                    break;
            }
        }

        return $balances;
    }
}
