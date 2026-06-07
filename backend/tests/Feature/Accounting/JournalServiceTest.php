<?php

declare(strict_types=1);

use App\Enums\Accounting\JournalStatus;
use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\ImportBatch;
use App\Models\JournalEntry;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Accounting\FiscalYearGeneratorService;
use App\Services\Accounting\JournalService;
use App\Services\Accounting\PeriodLockService;
use App\ValueObjects\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

it('cannot post journal in locked period', function (): void {
    $lockService = app(PeriodLockService::class);

    $perm = Permission::firstOrCreate(
        ['name' => 'quarter.lock'],
        ['module' => 'quarter', 'action' => 'lock'],
    );
    $role = Role::firstOrCreate(['name' => 'super_admin'], ['display_name' => 'Super Admin']);
    $role->permissions()->syncWithoutDetaching($perm->id);
    $this->user->roles()->attach($role->id);

    // Create a draft journal entry
    $journal = $this->service->create([
        'accounting_period_id' => $this->period->id,
        'description' => 'Test journal',
        'journal_date' => '2024-01-15',
        'lines' => [
            ['account_id' => $this->cashAccount->id,    'debit' => '500000', 'credit' => '0'],
            ['account_id' => $this->revenueAccount->id, 'debit' => '0',     'credit' => '500000'],
        ],
    ], $this->user);

    // Approve it
    $this->service->submit($journal, $this->user);
    $this->service->approve($journal, $this->user);

    // Lock the period
    $lockService->lock($this->period, $this->user);

    // Try posting the journal, it should throw DomainException
    expect(fn () => $this->service->post($journal, $this->user))
        ->toThrow(DomainException::class, 'Cannot post to a locked period.');
});

it('imports chart of accounts from excel/csv', function (): void {
    Storage::fake('local');

    // Give user company.update permission
    $perm = Permission::firstOrCreate(
        ['name' => 'company.update'],
        ['module' => 'company', 'action' => 'update'],
    );
    $role = Role::firstOrCreate(['name' => 'super_admin'], ['display_name' => 'Super Admin']);
    $role->permissions()->syncWithoutDetaching($perm->id);
    $this->user->roles()->attach($role->id);

    // Create a mock CSV content
    $csvContent = "account_code,account_name,account_type,description,parent_code\n";
    $csvContent .= "1102,Cash at Bank,asset,Bank account,\n";
    $csvContent .= "1103,Petty Cash,asset,Small cash,1102\n";

    $file = UploadedFile::fake()->createWithContent('coa.csv', $csvContent);

    // Post import request
    $response = $this->actingAs($this->user)
        ->postJson(route('accounts.import', [$this->company]), [
            'file' => $file,
        ]);

    $response->assertStatus(201)
        ->assertJsonFragment(['status' => 'completed']);

    $batchId = $response->json('data.id');
    $batch = ImportBatch::find($batchId);
    expect($batch)->not->toBeNull();
    expect($batch->success_rows)->toBe(2);
    expect($batch->failed_rows)->toBe(0);

    // Verify accounts were created
    $account1 = ChartOfAccount::where('company_id', $this->company->id)->where('account_code', '1102')->first();
    expect($account1)->not->toBeNull();
    expect($account1->account_name)->toBe('Cash at Bank');

    $account2 = ChartOfAccount::where('company_id', $this->company->id)->where('account_code', '1103')->first();
    expect($account2)->not->toBeNull();
    expect($account2->parent_id)->toBe($account1->id);
});

it('imports journal entries from excel/csv', function (): void {
    Storage::fake('local');

    // Give user company.update permission
    $perm = Permission::firstOrCreate(
        ['name' => 'company.update'],
        ['module' => 'company', 'action' => 'update'],
    );
    $role = Role::firstOrCreate(['name' => 'super_admin'], ['display_name' => 'Super Admin']);
    $role->permissions()->syncWithoutDetaching($perm->id);
    $this->user->roles()->attach($role->id);

    // Create a mock CSV content with balanced debits and credits
    $csvContent = "journal_date,reference,description,account_code,debit,credit,line_description\n";
    $csvContent .= "2024-01-15,REF-001,Salary accrual,1101,500000,0,Salary payment\n";
    $csvContent .= "2024-01-15,REF-001,Salary accrual,4001,0,500000,Salary accrual offset\n";

    $file = UploadedFile::fake()->createWithContent('journals.csv', $csvContent);

    // Post import request
    $response = $this->actingAs($this->user)
        ->postJson(route('journals.import', [$this->company]), [
            'file' => $file,
        ]);

    $response->assertStatus(201)
        ->assertJsonFragment(['status' => 'completed']);

    $batchId = $response->json('data.id');
    $batch = ImportBatch::find($batchId);
    expect($batch)->not->toBeNull();
    expect($batch->success_rows)->toBe(2);
    expect($batch->failed_rows)->toBe(0);

    // Verify journal was created
    $journal = JournalEntry::where('company_id', $this->company->id)
        ->where('reference', 'REF-001')
        ->first();
    expect($journal)->not->toBeNull();
    expect($journal->description)->toBe('Salary accrual');
    expect($journal->lines()->count())->toBe(2);
});
