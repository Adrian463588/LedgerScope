<?php

declare(strict_types=1);

use App\Enums\Common\UserStatus;
use App\Models\Company;
use App\Models\Engagement;
use App\Models\Role;
use App\Models\User;
use App\Services\Auth\TotpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

it('returns mfa_required when user has MFA enabled', function (): void {
    $user = User::factory()->create([
        'email' => 'mfa@test.com',
        'password' => bcrypt('Password123!'),
        'mfa_enabled' => true,
        'mfa_secret' => 'B32SECRET',
        'status' => UserStatus::Active,
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'mfa@test.com',
        'password' => 'Password123!',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.mfa_required', true)
        ->assertJsonPath('data.email', 'mfa@test.com');

    // User should not be logged in yet
    expect(Auth::check())->toBeFalse();
});

it('verifies MFA code and logs the user in', function (): void {
    $user = User::factory()->create([
        'email' => 'mfa@test.com',
        'mfa_enabled' => true,
        'mfa_secret' => 'JBSWY3DPEHPK3PXP', // Base32 of "Hello!"
        'status' => UserStatus::Active,
    ]);

    // To ensure the request has a session started, we send Referer header (Sanctum stateful SPA check)
    $headers = [
        'Referer' => 'http://localhost',
    ];

    $this->withSession(['mfa:user_id' => $user->id]);

    // Calculate valid OTP code
    $totpService = new TotpService();
    $timeSlice = floor(time() / 30);
    $key = pack('N*', 0) . pack('N*', (int)$timeSlice);
    // Decode secret key
    $secretKey = '';
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $decodedSecret = '';
    $val = 0;
    $bits = 0;
    for ($i = 0; $i < strlen($user->mfa_secret); $i++) {
        $val = ($val << 5) | strpos($alphabet, $user->mfa_secret[$i]);
        $bits += 5;
        if ($bits >= 8) {
            $bits -= 8;
            $decodedSecret .= chr(($val >> $bits) & 0xff);
        }
    }
    
    $hmac = hash_hmac('sha1', $key, $decodedSecret, true);
    $offset = ord($hmac[19]) & 0xf;
    $otpPart = (
        (ord($hmac[$offset]) & 0x7f) << 24 |
        (ord($hmac[$offset + 1]) & 0xff) << 16 |
        (ord($hmac[$offset + 2]) & 0xff) << 8 |
        (ord($hmac[$offset + 3]) & 0xff)
    );
    $otp = $otpPart % 1000000;
    $validCode = str_pad((string)$otp, 6, '0', STR_PAD_LEFT);

    $response = $this->postJson('/api/v1/auth/mfa/verify', [
        'code' => $validCode,
    ], $headers);

    $response->assertStatus(200)
        ->assertJsonPath('success', true);
});

it('fails MFA verification for invalid code', function (): void {
    $user = User::factory()->create([
        'mfa_enabled' => true,
        'mfa_secret' => 'JBSWY3DPEHPK3PXP',
        'status' => UserStatus::Active,
    ]);

    $headers = [
        'Referer' => 'http://localhost',
    ];

    $this->withSession(['mfa:user_id' => $user->id]);

    $response = $this->postJson('/api/v1/auth/mfa/verify', [
        'code' => '000000', // Invalid code
    ], $headers);

    $response->assertStatus(401)
        ->assertJsonPath('success', false);
});

it('enforces session inactivity timeout', function (): void {
    $user = User::factory()->create([
        'email' => 'timeout@test.com',
        'password' => bcrypt('Password123!'),
        'status' => UserStatus::Active,
    ]);
    
    $headers = [
        'Referer' => 'http://localhost',
    ];

    // Log in via real endpoint to initialize session
    $this->postJson('/api/v1/auth/login', [
        'email' => 'timeout@test.com',
        'password' => 'Password123!',
    ], $headers)->assertStatus(200);

    // Set a last_activity 20 minutes ago (default limit is 15 minutes)
    $this->withSession(['last_activity' => time() - (20 * 60)]);

    // Next request should return 401 session_expired
    $this->getJson('/api/v1/auth/me', $headers)
        ->assertStatus(401)
        ->assertJsonPath('code', 'session_expired');
});

it('allows user access when not timed out', function (): void {
    $user = User::factory()->create([
        'email' => 'timeout2@test.com',
        'password' => bcrypt('Password123!'),
        'status' => UserStatus::Active,
    ]);
    
    $headers = [
        'Referer' => 'http://localhost',
    ];

    $this->postJson('/api/v1/auth/login', [
        'email' => 'timeout2@test.com',
        'password' => 'Password123!',
    ], $headers)->assertStatus(200);

    $this->withSession(['last_activity' => time() - (5 * 60)]);

    $this->getJson('/api/v1/auth/me', $headers)
        ->assertStatus(200);
});

it('restricts engagements routes from client users', function (): void {
    $clientRole = Role::firstOrCreate(['name' => 'client'], [
        'display_name' => 'Client',
        'description' => 'Client user',
        'is_system' => true,
    ]);

    $user = User::factory()->create(['status' => UserStatus::Active]);
    $user->roles()->attach($clientRole->id);

    $company = Company::factory()->create();
    $lead = User::factory()->create();
    $engagement = Engagement::create([
        'company_id' => $company->id,
        'name' => 'Audit 2026',
        'engagement_type' => 'audit',
        'status' => 'planning',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'lead_auditor_id' => $lead->id,
    ]);

    $this->actingAs($user)
        ->getJson("/api/v1/engagements/{$engagement->id}")
        ->assertStatus(403)
        ->assertJsonPath('success', false);
});
