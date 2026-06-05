<?php

declare(strict_types=1);

use App\Enums\Accounting\JournalStatus;
use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Accounting\FiscalYearGeneratorService;
use App\Services\Accounting\JournalService;
use App\Services\Accounting\PeriodLockService;
use App\ValueObjects\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->company = Company::factory()->create(['currency' => 'IDR']);
    $this->company->companyUsers()->create(['user_id' => $this->user->id]);

    // Generate fiscal year + period
    $fyService = app(FiscalYearGeneratorService::class);
    $fiscalYear = $fyService->generate($this->company, 2024);
    $this->period = $fiscalYear->accountingPeriods()->where('period_name', '2024-01')->first();

    // Create two COA accounts
    $this->cashAccount = ChartOfAccount::create([
        'company_id' => $this->company->id,
        'account_code' => '1101',
        'account_name' => 'Cash',
        'account_type' => 'asset',
        'is_active' => true,
    ]);

    $this->revenueAccount = ChartOfAccount::create([
        'company_id' => $this->company->id,
        'account_code' => '4001',
        'account_name' => 'Revenue',
        'account_type' => 'revenue',
        'is_active' => true,
    ]);

    $this->service = app(JournalService::class);
});

it('Money ValueObject add is bcmath accurate', function (): void {
    $a = new Money('100.00', 'IDR');
    $b = new Money('200.50', 'IDR');

    expect($a->add($b)->getAmount())->toBe('300.50');
    expect(Money::zero('IDR')->isZero())->toBeTrue();
});

it('Money throws on currency mismatch', function (): void {
    $a = new Money('100.00', 'IDR');
    $b = new Money('100.00', 'USD');

    expect(fn () => $a->add($b))->toThrow(InvalidArgumentException::class);
});

it('creates a draft journal entry', function (): void {
    $journal = $this->service->create([
        'accounting_period_id' => $this->period->id,
        'description' => 'Test journal',
        'journal_date' => '2024-01-15',
        'lines' => [
            ['account_id' => $this->cashAccount->id,    'debit' => '500000', 'credit' => '0'],
            ['account_id' => $this->revenueAccount->id, 'debit' => '0',     'credit' => '500000'],
        ],
    ], $this->user);

    expect($journal->status)->toBe(JournalStatus::Draft);
    expect($journal->lines)->toHaveCount(2);
});

it('cannot create journal in locked period', function (): void {
    $lockService = app(PeriodLockService::class);

    $perm = Permission::firstOrCreate(
        ['name' => 'quarter.lock'],
        ['module' => 'quarter', 'action' => 'lock'],
    );
    $role = Role::firstOrCreate(['name' => 'super_admin'], ['display_name' => 'Super Admin']);
    $role->permissions()->syncWithoutDetaching($perm->id);
    $this->user->roles()->attach($role->id);

    $lockService->lock($this->period, $this->user);

    expect(fn () => $this->service->create([
        'accounting_period_id' => $this->period->id,
        'description' => 'Bad journal',
        'journal_date' => '2024-01-10',
        'lines' => [
            ['account_id' => $this->cashAccount->id,    'debit' => '1000', 'credit' => '0'],
            ['account_id' => $this->revenueAccount->id, 'debit' => '0',   'credit' => '1000'],
        ],
    ], $this->user))->toThrow(DomainException::class);
});

it('post fails when debit != credit', function (): void {
    // Force status to Approved using forceFill to bypass immutability check
    $journal = JournalEntry::create([
        'company_id' => $this->company->id,
        'accounting_period_id' => $this->period->id,
        'description' => 'Unbalanced',
        'journal_date' => '2024-01-10',
        'status' => JournalStatus::Approved->value,
        'source_type' => 'manual',
        'created_by' => $this->user->id,
        'approved_by' => $this->user->id,
    ]);

    $journal->lines()->create([
        'account_id' => $this->cashAccount->id,
        'debit' => '500',
        'credit' => '0',
        'currency' => 'IDR',
    ]);
    $journal->lines()->create([
        'account_id' => $this->revenueAccount->id,
        'debit' => '0',
        'credit' => '300',   // unbalanced — 500 != 300
        'currency' => 'IDR',
    ]);

    expect(fn () => $this->service->post($journal, $this->user))
        ->toThrow(DomainException::class, 'does not balance');
});

it('post requires minimum 2 lines', function (): void {
    $journal = JournalEntry::create([
        'company_id' => $this->company->id,
        'accounting_period_id' => $this->period->id,
        'description' => 'Single line',
        'journal_date' => '2024-01-10',
        'status' => JournalStatus::Approved->value,
        'source_type' => 'manual',
        'created_by' => $this->user->id,
        'approved_by' => $this->user->id,
    ]);

    $journal->lines()->create([
        'account_id' => $this->cashAccount->id,
        'debit' => '500',
        'credit' => '0',
        'currency' => 'IDR',
    ]);

    expect(fn () => $this->service->post($journal, $this->user))
        ->toThrow(DomainException::class, 'at least 2 lines');
});

it('posted journal cannot be mutated', function (): void {
    $journal = JournalEntry::create([
        'company_id' => $this->company->id,
        'accounting_period_id' => $this->period->id,
        'description' => 'Already posted',
        'journal_date' => '2024-01-10',
        'journal_number' => 'JNL-1-2024-00001',
        'status' => JournalStatus::Posted->value,
        'source_type' => 'manual',
        'created_by' => $this->user->id,
        'posted_by' => $this->user->id,
        'posted_at' => now(),
    ]);

    expect(fn () => $journal->update(['description' => 'Changed']))
        ->toThrow(DomainException::class, 'immutable');
});
