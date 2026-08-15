<?php

declare(strict_types=1);

use App\Models\AccountingPeriod;
use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\FiscalYear;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Role;
use App\Models\User;
use App\Services\Accounting\JournalRedFlagService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── Helpers ──────────────────────────────────────────────────────────────────

function makeRedFlagAdmin(): User
{
    $user = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'firm_admin'], [
        'display_name' => 'Firm Admin',
        'is_system' => true,
    ]);
    $user->roles()->attach($role);

    return $user;
}

function makeSeedJournal(Company $company, AccountingPeriod $period, string $date, float $amount): JournalEntry
{
    $account = ChartOfAccount::firstOrCreate(
        ['code' => '1000', 'company_id' => $company->id],
        [
            'name' => 'Cash',
            'account_type' => 'asset',
            'normal_balance' => 'debit',
            'is_active' => true,
        ],
    );

    $journal = JournalEntry::create([
        'company_id' => $company->id,
        'accounting_period_id' => $period->id,
        'journal_number' => 'JE-TEST-'.uniqid(),
        'description' => 'Test journal',
        'journal_date' => $date,
        'status' => 'posted',
        'source_type' => 'manual',
        'created_by' => 1,
    ]);

    JournalEntryLine::create([
        'journal_entry_id' => $journal->id,
        'account_id' => $account->id,
        'debit' => $amount,
        'credit' => 0,
        'amount' => $amount,
        'description' => 'Test line',
    ]);

    return $journal;
}

// ─── Service Unit Tests ───────────────────────────────────────────────────────

test('red flag service detects weekend posting', function (): void {
    $company = Company::factory()->create();
    $fy = FiscalYear::create([
        'company_id' => $company->id,
        'year' => 2026,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'status' => 'open',
    ]);
    $period = AccountingPeriod::create([
        'fiscal_year_id' => $fy->id,
        'company_id' => $company->id,
        'name' => 'Jan 2026',
        'start_date' => '2026-01-01',
        'end_date' => '2026-01-31',
        'period_number' => 1,
        'status' => 'open',
    ]);

    // 2026-01-04 is a Sunday
    $journal = makeSeedJournal($company, $period, '2026-01-04', 500.00);

    $service = app(JournalRedFlagService::class);
    $flags = $service->scan(collect([$journal]));

    $rules = array_column($flags, 'rule');
    expect($rules)->toContain('weekend_posting');
});

test('red flag service detects round-number entries', function (): void {
    $company = Company::factory()->create();
    $fy = FiscalYear::create([
        'company_id' => $company->id,
        'year' => 2026,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'status' => 'open',
    ]);
    $period = AccountingPeriod::create([
        'fiscal_year_id' => $fy->id,
        'company_id' => $company->id,
        'name' => 'Jan 2026',
        'start_date' => '2026-01-01',
        'end_date' => '2026-01-31',
        'period_number' => 1,
        'status' => 'open',
    ]);

    // 2026-01-05 is a Monday — avoid weekend flag
    $journal = makeSeedJournal($company, $period, '2026-01-05', 50_000.00);

    $service = app(JournalRedFlagService::class);
    $flags = $service->scan(collect([$journal]));

    $rules = array_column($flags, 'rule');
    expect($rules)->toContain('round_number_entry');
});

test('red flag service detects large entries above threshold', function (): void {
    $company = Company::factory()->create();
    $fy = FiscalYear::create([
        'company_id' => $company->id,
        'year' => 2026,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'status' => 'open',
    ]);
    $period = AccountingPeriod::create([
        'fiscal_year_id' => $fy->id,
        'company_id' => $company->id,
        'name' => 'Jan 2026',
        'start_date' => '2026-01-01',
        'end_date' => '2026-01-31',
        'period_number' => 1,
        'status' => 'open',
    ]);

    $journal = makeSeedJournal($company, $period, '2026-01-05', 200_000.01);

    $service = app(JournalRedFlagService::class);
    $flags = $service->scan(collect([$journal]));

    $rules = array_column($flags, 'rule');
    expect($rules)->toContain('large_entry');
});

// ─── API Endpoint ─────────────────────────────────────────────────────────────

test('red flag scan endpoint returns 200 with scan results', function (): void {
    $admin = makeRedFlagAdmin();
    $company = Company::factory()->create();

    $response = $this->actingAs($admin)
        ->postJson("/api/v1/companies/{$company->id}/journals/red-flag-scan");

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['total_journals_scanned', 'total_flags', 'flags']]);
});
