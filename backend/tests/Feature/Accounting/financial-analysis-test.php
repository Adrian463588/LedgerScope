<?php

declare(strict_types=1);

use App\Models\AccountingPeriod;
use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\FinancialStatement;
use App\Models\FiscalYear;
use App\Models\Role;
use App\Models\TrialBalance;
use App\Models\TrialBalanceLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    /** @var TestCase $this */
    $this->user = User::factory()->create();
    $adminRole = Role::firstOrCreate(['name' => 'super_admin'], ['display_name' => 'Super Admin']);
    $this->user->roles()->attach($adminRole->id);

    $this->company = Company::factory()->create(['currency' => 'IDR']);

    $this->fy = FiscalYear::create([
        'company_id' => $this->company->id,
        'year' => 2024,
        'start_date' => '2024-01-01',
        'end_date' => '2024-12-31',
        'status' => 'active',
    ]);

    $this->period = AccountingPeriod::create([
        'company_id' => $this->company->id,
        'fiscal_year_id' => $this->fy->id,
        'period_name' => '2024-01',
        'period_type' => 'monthly',
        'start_date' => '2024-01-01',
        'end_date' => '2024-01-31',
        'status' => 'open',
    ]);

    $this->tb = TrialBalance::create([
        'company_id' => $this->company->id,
        'accounting_period_id' => $this->period->id,
        'total_debit' => '50000.00',
        'total_credit' => '50000.00',
        'is_balanced' => true,
        'status' => 'balanced',
        'generated_at' => now(),
        'generated_by' => $this->user->id,
    ]);

    $this->cash = ChartOfAccount::create([
        'company_id' => $this->company->id,
        'account_code' => '1101',
        'account_name' => 'Cash',
        'account_type' => 'asset',
        'is_active' => true,
    ]);

    $this->revenue = ChartOfAccount::create([
        'company_id' => $this->company->id,
        'account_code' => '4001',
        'account_name' => 'Revenue',
        'account_type' => 'revenue',
        'is_active' => true,
    ]);

    TrialBalanceLine::create([
        'trial_balance_id' => $this->tb->id,
        'account_id' => $this->cash->id,
        'opening_debit' => '0.00',
        'opening_credit' => '0.00',
        'period_debit' => '50000.00',
        'period_credit' => '0.00',
        'closing_debit' => '50000.00',
        'closing_credit' => '0.00',
    ]);

    TrialBalanceLine::create([
        'trial_balance_id' => $this->tb->id,
        'account_id' => $this->revenue->id,
        'opening_debit' => '0.00',
        'opening_credit' => '0.00',
        'period_debit' => '0.00',
        'period_credit' => '50000.00',
        'closing_debit' => '0.00',
        'closing_credit' => '50000.00',
    ]);
});

it('calculates financial ratios correctly via API', function (): void {
    /** @var TestCase $this */
    $response = $this->actingAs($this->user)
        ->getJson("/api/v1/companies/{$this->company->id}/financial-analysis/ratios");

    $response->assertStatus(200);
    $response->assertJsonPath('data.current_ratio', 'N/A');
});

it('fetches financial trends via API', function (): void {
    /** @var TestCase $this */
    $response = $this->actingAs($this->user)
        ->getJson("/api/v1/companies/{$this->company->id}/financial-analysis/trends");

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['labels', 'revenues', 'expenses', 'net_incomes'],
    ]);
});

it('generates a financial statement and retrieves it with comparison', function (): void {
    /** @var TestCase $this */
    $statement = FinancialStatement::create([
        'company_id' => $this->company->id,
        'accounting_period_id' => $this->period->id,
        'statement_type' => 'income_statement',
        'status' => 'draft',
        'version' => 1,
        'is_locked' => false,
        'data' => ['net_income' => '50000.00'],
        'generated_by' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson("/api/v1/companies/{$this->company->id}/financial-statements/{$statement->id}");

    $response->assertStatus(200);
    $response->assertJsonPath('data.version', 1);

    $comparePeriod = AccountingPeriod::create([
        'company_id' => $this->company->id,
        'fiscal_year_id' => $this->fy->id,
        'period_name' => '2023-12',
        'period_type' => 'monthly',
        'start_date' => '2023-12-01',
        'end_date' => '2023-12-31',
        'status' => 'closed',
    ]);

    $compareStatement = FinancialStatement::create([
        'company_id' => $this->company->id,
        'accounting_period_id' => $comparePeriod->id,
        'statement_type' => 'income_statement',
        'status' => 'approved',
        'version' => 1,
        'is_locked' => true,
        'data' => ['net_income' => '40000.00'],
        'generated_by' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson("/api/v1/companies/{$this->company->id}/financial-statements/{$statement->id}?compare_with={$comparePeriod->id}");

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'statement',
            'comparison',
        ],
    ]);
    $response->assertJsonPath('data.comparison.data.net_income', '40000.00');
});

it('exports financial statements to PDF and Excel', function (): void {
    /** @var TestCase $this */
    $statement = FinancialStatement::create([
        'company_id' => $this->company->id,
        'accounting_period_id' => $this->period->id,
        'statement_type' => 'income_statement',
        'status' => 'draft',
        'version' => 1,
        'is_locked' => false,
        'data' => ['revenue' => ['lines' => [], 'total' => '50000.00'], 'net_income' => '50000.00'],
        'generated_by' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)
        ->get("/api/v1/companies/{$this->company->id}/financial-statements/{$statement->id}/export?format=pdf");

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');

    $response = $this->actingAs($this->user)
        ->get("/api/v1/companies/{$this->company->id}/financial-statements/{$statement->id}/export?format=xlsx");

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

it('calculates financial variance via API', function (): void {
    /** @var TestCase $this */
    $response = $this->actingAs($this->user)
        ->getJson("/api/v1/companies/{$this->company->id}/financial-analysis/variance");

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'period',
            'compare_period',
            'variances' => [
                '*' => ['category', 'current', 'compare', 'variance', 'percentage'],
            ],
        ],
    ]);
});
