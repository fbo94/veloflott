<?php

declare(strict_types=1);

test('application returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('api health check endpoint', function () {
    $response = $this->get('/api/health');

    $response->assertStatus(200)
        ->assertJson(['status' => 'ok']);
});
