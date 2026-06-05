<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Enums\Accounting\PeriodStatus;
use App\Enums\Accounting\PeriodType;
use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\FiscalYear;
use App\Models\Quarter;
use App\Models\QuarterClosingChecklist;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * FiscalYearGeneratorService — creates fiscal year with 12 periods + 4 quarters.
 *
 * AGENTS.md Phase 3 §3.2
 */
final class FiscalYearGeneratorService
{
    /** Default checklist keys per quarter (AGENTS.md §3.2) */
    private const CHECKLIST_KEYS = [
        'all_journals_posted',
        'imported_data_validated',
        'trial_balance_balanced',
        'bank_reconciliation_completed',
        'ar_reconciliation_completed',
        'ap_reconciliation_completed',
        'tax_account_reviewed',
        'accrual_entries_posted',
        'prepayment_entries_posted',
        'depreciation_entries_posted',
        'financial_statements_generated',
        'manager_review_completed',
        'quarter_approved',
        'quarter_locked',
    ];

    /** Quarter month mapping */
    private const QUARTER_MONTHS = [
        'Q1' => [1, 2, 3],
        'Q2' => [4, 5, 6],
        'Q3' => [7, 8, 9],
        'Q4' => [10, 11, 12],
    ];

    public function generate(Company $company, int $year): FiscalYear
    {
        return DB::transaction(function () use ($company, $year): FiscalYear {
            $startMonth = $company->fiscal_year_start_month ?? 1;

            $startDate = Carbon::create($year, $startMonth, 1)->startOfDay();
            $endDate = $startDate->copy()->addYear()->subDay()->endOfDay();

            // 1. Create fiscal year
            /** @var FiscalYear $fiscalYear */
            $fiscalYear = FiscalYear::create([
                'company_id' => $company->id,
                'year' => $year,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ]);

            // 2. Create 4 quarters
            $quarters = $this->createQuarters($company->id, $fiscalYear, $startDate);

            // 3. Create 12 monthly accounting periods
            $this->createMonthlyPeriods($company->id, $fiscalYear, $quarters, $startDate);

            // 4. Create checklist items for each quarter
            foreach ($quarters as $quarter) {
                $this->createChecklists($quarter);
            }

            return $fiscalYear->load(['quarters', 'accountingPeriods']);
        });
    }

    /**
     * @return array<string, Quarter>
     */
    private function createQuarters(int $companyId, FiscalYear $fiscalYear, Carbon $fyStart): array
    {
        $quarters = [];

        foreach (self::QUARTER_MONTHS as $code => $relativeMonths) {
            // Q1 = months 1-3 of FY, Q2 = months 4-6, etc.
            $qStart = $fyStart->copy()->addMonths($relativeMonths[0] - 1);
            $qEnd = $fyStart->copy()->addMonths($relativeMonths[2] - 1)->endOfMonth();

            /** @var Quarter $quarter */
            $quarter = Quarter::create([
                'company_id' => $companyId,
                'fiscal_year_id' => $fiscalYear->id,
                'quarter_code' => $code,
                'start_date' => $qStart->toDateString(),
                'end_date' => $qEnd->toDateString(),
                'status' => PeriodStatus::Open->value,
            ]);

            $quarters[$code] = $quarter;
        }

        return $quarters;
    }

    /**
     * @param  array<string, Quarter>  $quarters
     */
    private function createMonthlyPeriods(int $companyId, FiscalYear $fiscalYear, array $quarters, Carbon $fyStart): void
    {
        for ($monthOffset = 0; $monthOffset < 12; $monthOffset++) {
            $periodStart = $fyStart->copy()->addMonths($monthOffset);
            $periodEnd = $periodStart->copy()->endOfMonth();

            // Map month (1-based within FY) to quarter
            $fyMonth = $monthOffset + 1;
            $quarterCode = $this->monthToQuarterCode($fyMonth);
            $quarter = $quarters[$quarterCode] ?? null;

            AccountingPeriod::create([
                'company_id' => $companyId,
                'fiscal_year_id' => $fiscalYear->id,
                'quarter_id' => $quarter?->id,
                'period_name' => $periodStart->format('Y-m'),
                'period_type' => PeriodType::Monthly->value,
                'start_date' => $periodStart->toDateString(),
                'end_date' => $periodEnd->toDateString(),
                'status' => PeriodStatus::Open->value,
            ]);
        }
    }

    private function monthToQuarterCode(int $fyMonth): string
    {
        return match (true) {
            $fyMonth <= 3 => 'Q1',
            $fyMonth <= 6 => 'Q2',
            $fyMonth <= 9 => 'Q3',
            default => 'Q4',
        };
    }

    private function createChecklists(Quarter $quarter): void
    {
        $rows = array_map(
            fn (string $key) => [
                'quarter_id' => $quarter->id,
                'checklist_key' => $key,
                'is_completed' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            self::CHECKLIST_KEYS,
        );

        QuarterClosingChecklist::insert($rows);
    }
}
