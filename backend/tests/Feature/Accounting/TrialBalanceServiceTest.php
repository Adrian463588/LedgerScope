<?php

declare(strict_types=1);

use App\Enums\Accounting\JournalStatus;
use App\Models\AccountingPeriod;
use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\User;
use App\Services\Accounting\FiscalYearGeneratorService;
use App\Services\Accounting\TrialBalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->company = Company::factory()->create(['currency' => 'IDR']);

    $fyService = app(FiscalYearGeneratorService::class);
    $fy = $fyService->generate($this->company, 2024);
    $this->period = AccountingPeriod::where('fiscal_year_id', $fy->id)
        ->where('period_name', '2024-01')->first();

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

    $this->service = app(TrialBalanceService::class);
});

it('generates trial balance with correct debit/credit totals', function (): void {
    // Post a balanced journal manually
    $journal = JournalEntry::create([
        'company_id' => $this->company->id,
        'accounting_period_id' => $this->period->id,
        'description' => 'Revenue recognised',
        'journal_date' => '2024-01-15',
        'journal_number' => 'JNL-1-2024-00001',
        'status' => JournalStatus::Posted->value,
        'source_type' => 'manual',
        'created_by' => $this->user->id,
        'posted_by' => $this->user->id,
        'posted_at' => now(),
    ]);

    $journal->lines()->createMany([
        ['account_id' => $this->cash->id,    'debit' => '5000000', 'credit' => '0',       'currency' => 'IDR'],
        ['account_id' => $this->revenue->id, 'debit' => '0',       'credit' => '5000000', 'currency' => 'IDR'],
    ]);

    $tb = $this->service->generate($this->company, $this->period, $this->user);

    expect($tb->is_balanced)->toBeTrue();
    expect($tb->total_debit)->toBe('5000000.00');
    expect($tb->total_credit)->toBe('5000000.00');
    expect($tb->lines)->toHaveCount(2);
});

it('trial balance is_balanced false when lines not equal', function (): void {
    // Inject broken data directly (bypass service validation)
    $journal = JournalEntry::create([
        'company_id' => $this->company->id,
        'accounting_period_id' => $this->period->id,
        'description' => 'Broken',
        'journal_date' => '2024-01-10',
        'journal_number' => 'JNL-1-2024-00002',
        'status' => JournalStatus::Posted->value,
        'source_type' => 'manual',
        'created_by' => $this->user->id,
        'posted_by' => $this->user->id,
        'posted_at' => now(),
    ]);

    // Intentionally unbalanced (raw insert bypasses domain checks)
    DB::table('journal_entry_lines')->insert([
        'journal_entry_id' => $journal->id,
        'account_id' => $this->cash->id,
        'debit' => '1000.00',
        'credit' => '0.00',
        'currency' => 'IDR',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $tb = $this->service->generate($this->company, $this->period, $this->user);

    expect($tb->is_balanced)->toBeFalse();
});

it('only includes posted journal lines in trial balance', function (): void {
    // Draft journal — should be excluded
    JournalEntry::create([
        'company_id' => $this->company->id,
        'accounting_period_id' => $this->period->id,
        'description' => 'Draft — ignore',
        'journal_date' => '2024-01-05',
        'status' => JournalStatus::Draft->value,
        'source_type' => 'manual',
        'created_by' => $this->user->id,
    ]);

    $tb = $this->service->generate($this->company, $this->period, $this->user);

    // No posted lines → balances are zero, balanced
    expect($tb->is_balanced)->toBeTrue();
    expect($tb->total_debit)->toBe('0.00');
    expect($tb->lines)->toHaveCount(0);
});
