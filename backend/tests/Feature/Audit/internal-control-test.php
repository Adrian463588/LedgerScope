<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Engagement;
use App\Models\InternalControl;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── Helpers ──────────────────────────────────────────────────────────────────

function makeAdminUser(): User
{
    $user = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'firm_admin'], [
        'display_name' => 'Firm Admin',
        'description' => 'Firm administrator',
        'is_system' => true,
    ]);
    $user->roles()->attach($role);

    return $user;
}

function makeEngagement(User $user): Engagement
{
    $company = Company::factory()->create();

    return Engagement::create([
        'company_id' => $company->id,
        'name' => 'Audit 2026',
        'engagement_type' => 'audit',
        'status' => 'planning',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'lead_auditor_id' => $user->id,
    ]);
}

// ─── Internal Controls CRUD ───────────────────────────────────────────────────

test('can list internal controls for an engagement', function (): void {
    $user = makeAdminUser();
    $engagement = makeEngagement($user);

    InternalControl::create([
        'engagement_id' => $engagement->id,
        'name' => 'Segregation of Duties',
        'control_type' => 'preventive',
    ]);

    $response = $this->actingAs($user)
        ->getJson("/api/v1/engagements/{$engagement->id}/internal-controls");

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.0.name', 'Segregation of Duties');
});

test('can create an internal control', function (): void {
    $user = makeAdminUser();
    $engagement = makeEngagement($user);

    $response = $this->actingAs($user)
        ->postJson("/api/v1/engagements/{$engagement->id}/internal-controls", [
            'name' => 'Monthly Bank Reconciliation',
            'control_type' => 'detective',
            'category' => 'financial reporting',
            'frequency' => 'monthly',
            'effectiveness' => 'effective',
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.name', 'Monthly Bank Reconciliation');

    $this->assertDatabaseHas('internal_controls', ['name' => 'Monthly Bank Reconciliation']);
});

test('can update an internal control', function (): void {
    $user = makeAdminUser();
    $engagement = makeEngagement($user);
    $control = InternalControl::create([
        'engagement_id' => $engagement->id,
        'name' => 'Access Control Review',
        'control_type' => 'preventive',
    ]);

    $response = $this->actingAs($user)
        ->putJson("/api/v1/engagements/{$engagement->id}/internal-controls/{$control->id}", [
            'effectiveness' => 'partially_effective',
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.effectiveness', 'partially_effective');
});

test('can delete an internal control', function (): void {
    $user = makeAdminUser();
    $engagement = makeEngagement($user);
    $control = InternalControl::create([
        'engagement_id' => $engagement->id,
        'name' => 'To Be Deleted',
        'control_type' => 'corrective',
    ]);

    $response = $this->actingAs($user)
        ->deleteJson("/api/v1/engagements/{$engagement->id}/internal-controls/{$control->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('internal_controls', ['id' => $control->id]);
});

// ─── Control Risks ────────────────────────────────────────────────────────────

test('can add a risk to an internal control', function (): void {
    $user = makeAdminUser();
    $engagement = makeEngagement($user);
    $control = InternalControl::create([
        'engagement_id' => $engagement->id,
        'name' => 'AP Approval Control',
        'control_type' => 'preventive',
    ]);

    $response = $this->actingAs($user)
        ->postJson("/api/v1/engagements/{$engagement->id}/internal-controls/{$control->id}/risks", [
            'risk_name' => 'Unauthorised payment',
            'likelihood' => 'high',
            'impact' => 'high',
            'residual_risk' => 'medium',
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.risk_name', 'Unauthorised payment');

    $this->assertDatabaseHas('control_risks', ['risk_name' => 'Unauthorised payment']);
});
