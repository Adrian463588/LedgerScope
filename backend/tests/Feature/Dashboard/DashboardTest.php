<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\User;
use App\Services\Accounting\FiscalYearGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns dashboard data for an authorized company and accounting period', function (): void {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $user->companies()->attach($company);
    $fiscalYear = app(FiscalYearGeneratorService::class)->generate($company, 2024);
    $period = $fiscalYear->accountingPeriods()->firstOrFail();

    $response = $this->actingAs($user)->getJson('/api/v1/dashboard?company_id='.$company->id.'&period_id='.$period->id);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['kpis', 'quarterlySnapshot'],
        ]);
});

it('does not expose dashboard data for another company', function (): void {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $user->companies()->attach($company);
    $otherCompany = Company::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/v1/dashboard?company_id='.$otherCompany->id);

    $response->assertNotFound()
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'not_found');
});
