<?php

declare(strict_types=1);

use App\Models\User;

test('future integration status is explicit and operations fail closed without a provider', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson('/api/v1/future/integrations')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonFragment([
            'key' => 'erp',
            'mode' => 'unavailable',
            'configured' => false,
        ]);

    $this->actingAs($user)
        ->postJson('/api/v1/future/integrations/erp/execute', [
            'operation' => 'sync',
        ])
        ->assertStatus(501)
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'feature_unavailable')
        ->assertJsonMissingPath('trace');
});

test('future integrations have an authenticated Inertia entrypoint', function (): void {
    $this->actingAs(User::factory()->create())
        ->get('/future/integrations')
        ->assertOk()
        ->assertSee('LedgerScope Future Modules');
});
