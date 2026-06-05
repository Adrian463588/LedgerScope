<?php

declare(strict_types=1);

use App\Models\AccountingPeriod;
use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\User;
use App\Services\Accounting\FiscalYearGeneratorService;
use App\Services\Accounting\StatementBuilderService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user    = User::factory()->create();
    $this->company = Company::factory()->create(['currency' => 'IDR']);

    $fyService    = app(FiscalYearGeneratorService::class);
    $fy           = $fyService->generate($this->company, 2024);
    $this->period = AccountingPeriod::where('fiscal_year_id', $fy->id)
        ->where('period_name', '2024-01')->first();

    // COA: cash (asset), equity (equity), revenue (revenue), expense
    $this->cash    = ChartOfAccount::create(['company_id' => $this->company->id, 'account_code' => '1101', 'account_name' => 'Cash',    'account_type' => 'asset',   'is_active' => true]);
    $this->equity  = ChartOfAccount::create(['company_id' => $this->company->id, 'account_code' => '3001', 'account_name' => 'Equity',  'account_type' => 'equity',  'is_active' => true]);
    $this->revenue = ChartOfAccount::create(['company_id' => $this->company->id, 'account_code' => '4001', 'account_name' => 'Revenue', 'account_type' => 'revenue', 'is_active' => true]);
    $this->expense = ChartOfAccount::create(['company_id' => $this->company->id, 'account_code' => '5001', 'account_name' => 'Expense', 'account_type' => 'expense', 'is_active' => true]);

    // Post a journal: Debit Cash 1M, Credit Revenue 1M
    $journal = JournalEntry::create([
        'company_id'           => $this->company->id,
        'accounting_period_id' => $this->period->id,
        'description'          => 'Revenue recognised',
        'journal_date'         => '2024-01-10',
        'journal_number'       => 'JNL-1-2024-00001',
        'status'               => \App\Enums\Accounting\JournalStatus::Posted->value,
        'source_type'          => 'manual',
        'created_by'           => $this->user->id,
        'posted_by'            => $this->user->id,
        'posted_at'            => now(),
    ]);
    $journal->lines()->createMany([
        ['account_id' => $this->cash->id,    'debit' => '1000000', 'credit' => '0',       'currency' => 'IDR'],
        ['account_id' => $this->revenue->id, 'debit' => '0',       'credit' => '1000000', 'currency' => 'IDR'],
    ]);

    $this->service = app(StatementBuilderService::class);
});

it('builds income statement with net income', function (): void {
    $stmt = $this->service->build($this->company, $this->period, 'income_statement', $this->user);

    expect($stmt->statement_type)->toBe('income_statement');
    expect($stmt->data)->toHaveKey('revenue');
    expect($stmt->data['net_income'])->toBe('1000000.00');
});

it('builds balance sheet with assets = liabilities + equity', function (): void {
    $stmt = $this->service->build($this->company, $this->period, 'balance_sheet', $this->user);

    expect($stmt->statement_type)->toBe('balance_sheet');
    expect($stmt->data)->toHaveKey('assets');
    expect($stmt->data)->toHaveKey('liabilities_and_equity');
});

it('persists financial statement record', function (): void {
    $stmt = $this->service->build($this->company, $this->period, 'income_statement', $this->user);

    expect(\App\Models\FinancialStatement::where('id', $stmt->id)->exists())->toBeTrue();
    expect($stmt->status)->toBe('draft');
});
