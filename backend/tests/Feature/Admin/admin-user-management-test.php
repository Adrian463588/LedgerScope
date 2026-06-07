<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── Helpers ──────────────────────────────────────────────────────────────────

function makeSuperAdmin(): User
{
    $user = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'super_admin'], [
        'display_name' => 'Super Admin',
        'description'  => 'System super administrator',
        'is_system'    => true,
    ]);
    $user->roles()->attach($role);

    return $user;
}

function makeFirmAdmin(): User
{
    $user = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'firm_admin'], [
        'display_name' => 'Firm Admin',
        'description'  => 'Firm administrator',
        'is_system'    => true,
    ]);
    $user->roles()->attach($role);

    return $user;
}

// ─── Admin User Management ────────────────────────────────────────────────────

test('super admin can list all users', function (): void {
    $admin = makeSuperAdmin();
    User::factory()->count(3)->create();

    $response = $this->actingAs($admin)->getJson('/api/v1/admin/users');

    $response->assertStatus(200)
        ->assertJsonPath('success', true);
});

test('regular user cannot list admin users', function (): void {
    $regularUser = User::factory()->create();

    $response = $this->actingAs($regularUser)->getJson('/api/v1/admin/users');

    $response->assertStatus(403);
});

test('admin can update a user name', function (): void {
    $admin  = makeSuperAdmin();
    $target = User::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($admin)
        ->putJson("/api/v1/admin/users/{$target->id}", ['name' => 'New Name']);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'New Name');

    $this->assertDatabaseHas('users', ['id' => $target->id, 'name' => 'New Name']);
});

test('admin can suspend a user', function (): void {
    $admin  = makeSuperAdmin();
    $target = User::factory()->create();

    $response = $this->actingAs($admin)
        ->postJson("/api/v1/admin/users/{$target->id}/suspend");

    $response->assertStatus(200);
    $this->assertDatabaseHas('users', ['id' => $target->id, 'status' => 'suspended']);
});

test('admin can activate a suspended user', function (): void {
    $admin  = makeSuperAdmin();
    $target = User::factory()->create(['status' => 'suspended']);

    $response = $this->actingAs($admin)
        ->postJson("/api/v1/admin/users/{$target->id}/activate");

    $response->assertStatus(200);
    $this->assertDatabaseHas('users', ['id' => $target->id, 'status' => 'active']);
});

test('admin can list all roles', function (): void {
    makeSuperAdmin(); // ensure super_admin role exists
    $admin = makeSuperAdmin();

    $response = $this->actingAs($admin)->getJson('/api/v1/admin/roles');

    $response->assertStatus(200)
        ->assertJsonPath('success', true);
});

test('admin can assign a role to a user', function (): void {
    $admin  = makeSuperAdmin();
    $target = User::factory()->create();
    $role   = Role::firstOrCreate(['name' => 'partner'], [
        'display_name' => 'Partner',
        'is_system'    => true,
    ]);

    $response = $this->actingAs($admin)
        ->postJson("/api/v1/admin/users/{$target->id}/roles", ['role_id' => $role->id]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('user_roles', ['user_id' => $target->id, 'role_id' => $role->id]);
});

test('admin can revoke a role from a user', function (): void {
    $admin  = makeSuperAdmin();
    $target = User::factory()->create();
    $role   = Role::firstOrCreate(['name' => 'partner'], [
        'display_name' => 'Partner',
        'is_system'    => true,
    ]);
    $target->roles()->attach($role);

    $response = $this->actingAs($admin)
        ->deleteJson("/api/v1/admin/users/{$target->id}/roles/{$role->id}");

    $response->assertStatus(200);
    $this->assertDatabaseMissing('user_roles', ['user_id' => $target->id, 'role_id' => $role->id]);
});

// ─── Audit Trail ──────────────────────────────────────────────────────────────

test('firm admin can query the audit trail', function (): void {
    $admin = makeFirmAdmin();

    $response = $this->actingAs($admin)->getJson('/api/v1/admin/audit-trail');

    $response->assertStatus(200)
        ->assertJsonPath('success', true);
});

test('regular user cannot query the audit trail', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/v1/admin/audit-trail');

    $response->assertStatus(403);
});

test('audit trail can be filtered by action', function (): void {
    $admin = makeFirmAdmin();

    \App\Models\AuditLog::create([
        'action'     => 'admin.user.updated',
        'user_id'    => $admin->id,
        'created_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->getJson('/api/v1/admin/audit-trail?action=admin.user.updated');

    $response->assertStatus(200)
        ->assertJsonPath('success', true);
});
