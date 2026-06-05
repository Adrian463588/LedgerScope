<?php

declare(strict_types=1);

use App\Models\AccountingPeriod;
use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\Reconciliation;
use App\Models\User;
use App\Services\Accounting\FiscalYearGeneratorService;
use App\Services\Accounting\ReconciliationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user    = User::factory()->create();
    $this->company = Company::factory()->create(['currency' => 'IDR']);

    $fyService    = app(FiscalYearGeneratorService::class);
    $fy           = $fyService->generate($this->company, 2024);
    $this->period = AccountingPeriod::where('fiscal_year_id', $fy->id)
        ->where('period_name', '2024-01')->first();

    $this->cashAccount = ChartOfAccount::create([
        'company_id'   => $this->company->id,
        'account_code' => '1101',
        'account_name' => 'Cash at Bank',
        'account_type' => 'asset',
        'is_active'    => true,
    ]);

    $this->service = app(ReconciliationService::class);
});

it('creates a bank reconciliation record', function (): void {
    $rec = $this->service->create([
        'account_id'           => $this->cashAccount->id,
        'accounting_period_id' => $this->period->id,
        'reconciliation_type'  => 'bank',
        'book_balance'         => '10000000',
        'bank_balance'         => '10000000',
    ], $this->company, $this->user);

    expect($rec)->toBeInstanceOf(Reconciliation::class);
    expect($rec->status)->toBe('draft');
    expect($rec->company_id)->toBe($this->company->id);
});

it('computes difference between book and bank balance', function (): void {
    $rec = $this->service->create([
        'account_id'           => $this->cashAccount->id,
        'accounting_period_id' => $this->period->id,
        'reconciliation_type'  => 'bank',
        'book_balance'         => '10000000',
        'bank_balance'         => '9500000',
    ], $this->company, $this->user);

    expect($rec->difference)->toBe('500000.00');
});

it('approving reconciliation sets approved_by and status', function (): void {
    $rec = Reconciliation::create([
        'company_id'           => $this->company->id,
        'account_id'           => $this->cashAccount->id,
        'accounting_period_id' => $this->period->id,
        'reconciliation_type'  => 'bank',
        'status'               => 'draft',
        'book_balance'         => '5000000',
        'bank_balance'         => '5000000',
        'difference'           => '0.00',
    ]);

    $this->service->approve($rec, $this->user);

    $fresh = $rec->fresh();
    expect($fresh->status)->toBe('approved');
    expect($fresh->approved_by)->toBe($this->user->id);
    expect($fresh->approved_at)->not->toBeNull();
});
