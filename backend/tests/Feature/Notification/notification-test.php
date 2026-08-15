<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\DocumentRequest;
use App\Models\Engagement;
use App\Models\Role;
use App\Models\User;
use App\Notifications\AppNotification;
use App\Services\Audit\DocumentRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can list paginated notifications for the authenticated user', function () {
    $user = User::factory()->create();
    $user->notify(new AppNotification('Test Title', 'Test Message', 'test_type'));

    $response = $this->actingAs($user)
        ->getJson('/api/v1/notifications');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'success',
            'message',
            'data',
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
});

test('can mark notification as read', function () {
    $user = User::factory()->create();
    $user->notify(new AppNotification('Test Title', 'Test Message', 'test_type'));
    $notification = $user->notifications()->first();

    expect($notification->read_at)->toBeNull();

    $response = $this->actingAs($user)
        ->postJson("/api/v1/notifications/{$notification->id}/read");

    $response->assertStatus(200)
        ->assertJsonPath('success', true);

    expect($notification->fresh()->read_at)->not->toBeNull();
});

test('notification preferences use the API envelope and preserve critical alerts', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson('/api/v1/notifications/preferences')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data', []);

    $this->actingAs($user)
        ->putJson('/api/v1/notifications/preferences', [
            'preferences' => [
                [
                    'channel' => 'email',
                    'event_type' => 'document_request',
                    'enabled' => true,
                ],
            ],
        ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonFragment([
            'channel' => 'email',
            'event_type' => 'document_request',
            'enabled' => true,
        ]);

    $this->actingAs($user)
        ->putJson('/api/v1/notifications/preferences', [
            'preferences' => [
                [
                    'channel' => 'app',
                    'event_type' => 'finding',
                    'enabled' => false,
                ],
            ],
        ])
        ->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'domain_error');
});

test('overdue document requests trigger notifications to clients and auditors', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $engagement = Engagement::create([
        'company_id' => $company->id,
        'name' => 'Audit 2026',
        'engagement_type' => 'audit',
        'status' => 'planning',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'lead_auditor_id' => $user->id,
    ]);

    $role = Role::firstOrCreate(['name' => 'client'], [
        'display_name' => 'Client',
        'description' => 'Client user',
        'is_system' => true,
    ]);

    $clientUser = User::factory()->create();
    $engagement->company->users()->attach($clientUser, ['role_id' => $role->id, 'joined_at' => now()]);

    $request = DocumentRequest::create([
        'engagement_id' => $engagement->id,
        'company_id' => $engagement->company_id,
        'title' => 'Test Request',
        'description' => 'Test Description',
        'status' => 'requested',
        'due_date' => now()->subDay(),
        'requested_by' => $user->id,
        'assigned_to' => $clientUser->id,
    ]);

    $service = app(DocumentRequestService::class);
    $service->markOverdue();

    $clientNotifications = $clientUser->notifications()->get();
    $auditorNotifications = $user->notifications()->get();

    expect($clientNotifications)->toHaveCount(1);
    expect($auditorNotifications)->toHaveCount(1);

    expect($clientNotifications->first()->data['title'])->toBe('Document Request Overdue');
    expect($auditorNotifications->first()->data['title'])->toBe('Document Request Overdue');
});
