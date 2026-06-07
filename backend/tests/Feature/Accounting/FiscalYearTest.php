<?php

declare(strict_types=1);

use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\FiscalYear;
use App\Models\Permission;
use App\Models\Quarter;
use App\Models\Role;
use App\Models\User;
use App\Services\Accounting\FiscalYearGeneratorService;
use App\Services\Accounting\PeriodLockService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @property User $admin
 * @property Company $company
 */
uses(RefreshDatabase::class);

beforeEach(function (): void {
    /** @var TestCase $this */
    $this->admin = User::factory()->create();
    $adminRole = Role::firstOrCreate(['name' => 'super_admin'], ['display_name' => 'Super Admin']);
    $this->admin->roles()->attach($adminRole->id);

    $this->company = Company::factory()->create([
        'fiscal_year_start_month' => 1,
    ]);
});

it('generates exactly 12 monthly periods for a fiscal year', function (): void {
    $service = app(FiscalYearGeneratorService::class);
    $fiscalYear = $service->generate($this->company, 2024);

    expect($fiscalYear)->toBeInstanceOf(FiscalYear::class);
    expect($fiscalYear->accountingPeriods()->count())->toBe(12);
});

it('generates exactly 4 quarters for a fiscal year', function (): void {
    $service = app(FiscalYearGeneratorService::class);
    $fiscalYear = $service->generate($this->company, 2024);

    expect($fiscalYear->quarters()->count())->toBe(4);

    $codes = $fiscalYear->quarters()->pluck('quarter_code')->sort()->values()->all();
    expect($codes)->toBe(['Q1', 'Q2', 'Q3', 'Q4']);
});

it('quarter months map correctly to Q1-Q4', function (): void {
    $service = app(FiscalYearGeneratorService::class);
    $fiscalYear = $service->generate($this->company, 2024);

    $q1 = Quarter::where('fiscal_year_id', $fiscalYear->id)->where('quarter_code', 'Q1')->first();
    expect($q1->start_date->month)->toBe(1);
    expect($q1->end_date->month)->toBe(3);

    $q4 = Quarter::where('fiscal_year_id', $fiscalYear->id)->where('quarter_code', 'Q4')->first();
    expect($q4->start_date->month)->toBe(10);
    expect($q4->end_date->month)->toBe(12);
});

it('creates 14 checklist items per quarter', function (): void {
    $service = app(FiscalYearGeneratorService::class);
    $fiscalYear = $service->generate($this->company, 2024);

    /** @var Quarter $quarter */
    $quarter = $fiscalYear->quarters()->first();
    expect($quarter->checklists()->count())->toBe(14);
});

it('cannot create duplicate fiscal year for same company and year', function (): void {
    $service = app(FiscalYearGeneratorService::class);
    $service->generate($this->company, 2024);

    expect(fn () => $service->generate($this->company, 2024))
        ->toThrow(QueryException::class);
});

it('period lock service locks a period', function (): void {
    $service = app(FiscalYearGeneratorService::class);
    $fiscalYear = $service->generate($this->company, 2024);

    /** @var AccountingPeriod $period */
    $period = $fiscalYear->accountingPeriods()->first();

    // Assign lock permission
    $perm = Permission::firstOrCreate(
        ['name' => 'quarter.lock'],
        ['module' => 'quarter', 'action' => 'lock'],
    );
    $role = Role::firstOrCreate(['name' => 'super_admin'], ['display_name' => 'Super Admin']);
    $role->permissions()->syncWithoutDetaching($perm->id);

    $lockService = app(PeriodLockService::class);
    $lockService->lock($period, $this->admin);

    expect($period->fresh()->is_locked)->toBeTrue();
});

it('cannot lock an already-locked period', function (): void {
    $service = app(FiscalYearGeneratorService::class);
    $fiscalYear = $service->generate($this->company, 2024);
    /** @var AccountingPeriod $period */
    $period = $fiscalYear->accountingPeriods()->first();

    $perm = Permission::firstOrCreate(
        ['name' => 'quarter.lock'],
        ['module' => 'quarter', 'action' => 'lock'],
    );
    $role = Role::where('name', 'super_admin')->first();
    $role->permissions()->syncWithoutDetaching($perm->id);

    $lockService = app(PeriodLockService::class);
    $lockService->lock($period, $this->admin);

    expect(fn () => $lockService->lock($period->fresh(), $this->admin))
        ->toThrow(DomainException::class);
});

it('unlock requires a reason', function (): void {
    $service = app(FiscalYearGeneratorService::class);
    $fiscalYear = $service->generate($this->company, 2024);
    /** @var AccountingPeriod $period */
    $period = $fiscalYear->accountingPeriods()->first();

    $lockPerm = Permission::firstOrCreate(
        ['name' => 'quarter.lock'],
        ['module' => 'quarter', 'action' => 'lock'],
    );
    $unlockPerm = Permission::firstOrCreate(
        ['name' => 'quarter.unlock'],
        ['module' => 'quarter', 'action' => 'unlock'],
    );
    $role = Role::where('name', 'super_admin')->first();
    $role->permissions()->syncWithoutDetaching([$lockPerm->id, $unlockPerm->id]);

    $lockService = app(PeriodLockService::class);
    $lockService->lock($period, $this->admin);

    expect(fn () => $lockService->unlock($period->fresh(), $this->admin, ''))
        ->toThrow(DomainException::class, 'An unlock reason is required.');
});

it('quarter lock requires all checklist items completed', function (): void {
    // Link admin to company
    $this->company->companyUsers()->create(['user_id' => $this->admin->id]);

    // Assign permissions
    $compUpdatePerm = Permission::firstOrCreate(
        ['name' => 'company.update'],
        ['module' => 'company', 'action' => 'update'],
    );
    $lockPerm = Permission::firstOrCreate(
        ['name' => 'quarter.lock'],
        ['module' => 'quarter', 'action' => 'lock'],
    );
    $role = Role::where('name', 'super_admin')->first();
    $role->permissions()->syncWithoutDetaching([$compUpdatePerm->id, $lockPerm->id]);

    $service = app(FiscalYearGeneratorService::class);
    $fiscalYear = $service->generate($this->company, 2024);
    $quarter = $fiscalYear->quarters()->first();

    // Try to lock, should return 422 because checklist is incomplete
    $response = $this->actingAs($this->admin)
        ->postJson(route('quarters.lock', [$this->company, $quarter]));

    $response->assertStatus(422)
        ->assertJsonFragment(['success' => false]);

    // Mark all required checklist items completed
    $quarter->checklists()->where('is_required', true)->update(['is_completed' => true]);

    // Try to lock again, should succeed
    $response = $this->actingAs($this->admin)
        ->postJson(route('quarters.lock', [$this->company, $quarter]));

    $response->assertStatus(200)
        ->assertJsonFragment(['success' => true]);

    expect($quarter->fresh()->is_locked)->toBeTrue();
});
