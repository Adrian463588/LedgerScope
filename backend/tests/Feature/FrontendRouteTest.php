<?php

test('backend root exposes the service health contract', function () {
    $response = $this->getJson('/');

    $response->assertOk()
        ->assertJsonPath('status', 'online')
        ->assertJsonPath('service', 'LedgerScope API Backend');
});

test('api health exposes dependency statuses', function () {
    $response = $this->getJson('/api/health');

    $response->assertOk()
        ->assertJsonStructure(['status', 'timestamp', 'database', 'redis', 'queue', 'storage']);
});
