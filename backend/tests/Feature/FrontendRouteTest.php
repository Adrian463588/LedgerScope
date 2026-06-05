<?php

test('dashboard page renders via inertia', function () {
    $this->withoutVite();
    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});

test('journal entries page renders via inertia', function () {
    $this->withoutVite();
    $response = $this->get('/accounting/journals');
    $response->assertStatus(200);
});

test('create journal entry page renders via inertia', function () {
    $this->withoutVite();
    $response = $this->get('/accounting/journals/create');
    $response->assertStatus(200);
});
