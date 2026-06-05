<?php

declare(strict_types=1);

use App\Enums\Common\UserStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── RTK: RED Phase — these tests define expected behaviour ─────────────────

it('returns 200 and user data on successful login', function (): void {
    $role = Role::factory()->create(['name' => 'accountant']);
    $user = User::factory()->create([
        'email' => 'accountant@test.com',
        'password' => bcrypt('Password123!'),
        'status' => UserStatus::Active,
    ]);
    $user->roles()->attach($role->id);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'accountant@test.com',
        'password' => 'Password123!',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['id', 'name', 'email', 'roles', 'permissions'],
        ])
        ->assertJsonPath('success', true);
});

it('returns 401 for invalid credentials', function (): void {
    User::factory()->create(['email' => 'test@test.com']);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'test@test.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertJsonPath('success', false);
});

it('returns 422 for missing email field', function (): void {
    $response = $this->postJson('/api/v1/auth/login', [
        'password' => 'Password123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonStructure(['errors' => ['email']]);
});

it('returns 401 for suspended user', function (): void {
    User::factory()->create([
        'email' => 'suspended@test.com',
        'password' => bcrypt('Password123!'),
        'status' => UserStatus::Suspended,
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'suspended@test.com',
        'password' => 'Password123!',
    ]);

    $response->assertStatus(401);
});

it('locks out after 5 failed login attempts', function (): void {
    User::factory()->create(['email' => 'ratelimit@test.com']);

    // 5 failed attempts
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'ratelimit@test.com',
            'password' => 'wrongpassword',
        ]);
    }

    // 6th attempt should be throttled (429 Too Many Requests)
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'ratelimit@test.com',
        'password' => 'wrongpassword',
    ]);

    // ThrottleRequests middleware returns 429; LoginRequest throttle returns 422
    expect($response->status())->toBeIn([422, 429]);
});

it('returns 200 and clears session on logout', function (): void {
    $user = User::factory()->create(['status' => UserStatus::Active]);

    $response = $this->actingAs($user)->postJson('/api/v1/auth/logout');

    $response->assertStatus(200)
        ->assertJsonPath('success', true);
});

it('returns current user on GET /me', function (): void {
    $user = User::factory()->create(['status' => UserStatus::Active]);

    $response = $this->actingAs($user)->getJson('/api/v1/auth/me');

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', $user->email);
});

it('returns 401 for unauthenticated GET /me', function (): void {
    $response = $this->getJson('/api/v1/auth/me');

    $response->assertStatus(401);
});
