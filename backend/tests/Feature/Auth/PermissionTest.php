<?php

declare(strict_types=1);

use App\Enums\Common\UserStatus;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('user with correct permission returns true for hasPermission', function (): void {
    $role = Role::factory()->create();
    $permission = Permission::factory()->create(['name' => 'journal.post']);
    $role->permissions()->attach($permission->id);

    $user = User::factory()->create(['status' => UserStatus::Active]);
    $user->roles()->attach($role->id);
    $user->load('roles.permissions');

    expect($user->hasPermission('journal.post'))->toBeTrue();
});

it('user without permission returns false for hasPermission', function (): void {
    $user = User::factory()->create(['status' => UserStatus::Active]);
    $user->load('roles.permissions');

    expect($user->hasPermission('journal.post'))->toBeFalse();
});

it('super_admin role returns true via hasRole', function (): void {
    $role = Role::factory()->create(['name' => 'super_admin']);
    $user = User::factory()->create();
    $user->roles()->attach($role->id);
    $user->load('roles');

    expect($user->hasRole('super_admin'))->toBeTrue();
});

it('getAllPermissions returns unique permissions across multiple roles', function (): void {
    $role1 = Role::factory()->create();
    $role2 = Role::factory()->create();
    $perm1 = Permission::factory()->create(['name' => 'account.view']);
    $perm2 = Permission::factory()->create(['name' => 'journal.view']);

    $role1->permissions()->attach($perm1->id);
    $role2->permissions()->attach([$perm1->id, $perm2->id]);

    $user = User::factory()->create();
    $user->roles()->attach([$role1->id, $role2->id]);
    $user->load('roles.permissions');

    $permissions = $user->getAllPermissions();

    expect($permissions)->toHaveCount(2)
        ->and($permissions->contains('account.view'))->toBeTrue()
        ->and($permissions->contains('journal.view'))->toBeTrue();
});
