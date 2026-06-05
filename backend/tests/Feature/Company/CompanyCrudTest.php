<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @property \App\Models\User $superAdmin
 */

uses(RefreshDatabase::class);

beforeEach(function (): void {
    /** @var \Tests\TestCase $this */
    $this->superAdmin = User::factory()->create();
    $this->superAdmin->roles()->attach(
        Role::where('name', 'super_admin')->first()
            ?? Role::create(['name' => 'super_admin', 'display_name' => 'Super Admin']),
    );
});

it('super admin can create a company', function (): void {
    $response = $this->actingAs($this->superAdmin)->postJson('/api/v1/companies', [
        'name' => 'PT Test Maju',
        'currency' => 'IDR',
        'fiscal_year_start_month' => 1,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'PT Test Maju');

    $this->assertDatabaseHas('companies', ['name' => 'PT Test Maju']);
});

it('super admin can list companies', function (): void {
    Company::factory()->count(3)->create();

    $response = $this->actingAs($this->superAdmin)->getJson('/api/v1/companies');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data', 'meta' => ['total']]);
});

it('super admin can soft delete a company', function (): void {
    $company = Company::factory()->create();

    $this->actingAs($this->superAdmin)->deleteJson("/api/v1/companies/{$company->id}")
        ->assertOk();

    $this->assertSoftDeleted('companies', ['id' => $company->id]);
});

it('regular user cannot see another company data', function (): void {
    $otherCompany = Company::factory()->create();
    $regularUser = User::factory()->create();

    $this->actingAs($regularUser)->getJson("/api/v1/companies/{$otherCompany->id}")
        ->assertForbidden();
});

it('user can see company they belong to', function (): void {
    $company = Company::factory()->create();
    $regularUser = User::factory()->create();
    $regularUser->companies()->attach($company->id);

    // Assign basic role with company.view permission
    $role = Role::firstOrCreate(['name' => 'client'], ['display_name' => 'Client']);
    $perm = Permission::firstOrCreate(
        ['name' => 'company.view'],
        ['module' => 'company', 'action' => 'view'],
    );
    $role->permissions()->syncWithoutDetaching($perm->id);
    $regularUser->roles()->attach($role->id);

    $this->actingAs($regularUser)->getJson("/api/v1/companies/{$company->id}")
        ->assertOk();
});
