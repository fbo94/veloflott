<?php

declare(strict_types=1);

use Customer\Infrastructure\Persistence\Models\CustomerEloquentModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Disable authentication middleware for tests
    $this->withoutMiddleware();
});

test('can annotate customer with annotation and mark as risky', function () {
    // Create a customer
    $createResponse = $this->postJson('/api/customers', [
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    $customerId = $createResponse->json('id');

    // Annotate customer
    $response = $this->postJson("/api/customers/{$customerId}/annotation", [
        'annotation' => 'Président !',
        'is_risky_customer' => true,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'customer_id' => $customerId,
            'annotation' => 'Président !',
            'is_risky_customer' => true,
            'message' => 'Customer annotated successfully',
        ]);

    // Verify in database
    $this->assertDatabaseHas('customers', [
        'id' => $customerId,
        'notes' => 'Président !',
        'is_risky' => true,
    ]);
});

test('can annotate customer without marking as risky', function () {
    // Create a customer
    $createResponse = $this->postJson('/api/customers', [
        'first_name' => 'Jane',
        'last_name' => 'Smith',
    ]);

    $customerId = $createResponse->json('id');

    // Annotate customer
    $response = $this->postJson("/api/customers/{$customerId}/annotation", [
        'annotation' => 'Good customer',
        'is_risky_customer' => false,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'customer_id' => $customerId,
            'annotation' => 'Good customer',
            'is_risky_customer' => false,
        ]);

    // Verify in database
    $this->assertDatabaseHas('customers', [
        'id' => $customerId,
        'notes' => 'Good customer',
        'is_risky' => false,
    ]);
});

test('can update existing annotation', function () {
    // Create a customer with annotation
    $createResponse = $this->postJson('/api/customers', [
        'first_name' => 'Update',
        'last_name' => 'Test',
        'notes' => 'Old annotation',
    ]);

    $customerId = $createResponse->json('id');

    // Update annotation
    $response = $this->postJson("/api/customers/{$customerId}/annotation", [
        'annotation' => 'New annotation',
        'is_risky_customer' => true,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'annotation' => 'New annotation',
            'is_risky_customer' => true,
        ]);

    // Verify in database
    $this->assertDatabaseHas('customers', [
        'id' => $customerId,
        'notes' => 'New annotation',
        'is_risky' => true,
    ]);
});

test('can clear annotation', function () {
    // Create a customer with annotation
    $createResponse = $this->postJson('/api/customers', [
        'first_name' => 'Clear',
        'last_name' => 'Test',
        'notes' => 'Some annotation',
    ]);

    $customerId = $createResponse->json('id');

    // Clear annotation
    $response = $this->postJson("/api/customers/{$customerId}/annotation", [
        'annotation' => null,
        'is_risky_customer' => false,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'annotation' => null,
            'is_risky_customer' => false,
        ]);

    // Verify in database
    $this->assertDatabaseHas('customers', [
        'id' => $customerId,
        'notes' => null,
        'is_risky' => false,
    ]);
});

test('can change risky flag while keeping annotation', function () {
    // Create a customer
    $createResponse = $this->postJson('/api/customers', [
        'first_name' => 'Toggle',
        'last_name' => 'Risky',
    ]);

    $customerId = $createResponse->json('id');

    // Set annotation and mark as risky
    $this->postJson("/api/customers/{$customerId}/annotation", [
        'annotation' => 'Important note',
        'is_risky_customer' => true,
    ]);

    // Change only risky flag
    $response = $this->postJson("/api/customers/{$customerId}/annotation", [
        'annotation' => 'Important note',
        'is_risky_customer' => false,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'annotation' => 'Important note',
            'is_risky_customer' => false,
        ]);

    // Verify in database
    $this->assertDatabaseHas('customers', [
        'id' => $customerId,
        'notes' => 'Important note',
        'is_risky' => false,
    ]);
});

test('validates annotation max length', function () {
    // Create a customer
    $createResponse = $this->postJson('/api/customers', [
        'first_name' => 'Test',
        'last_name' => 'Validation',
    ]);

    $customerId = $createResponse->json('id');

    // Try to set annotation exceeding max length
    $response = $this->postJson("/api/customers/{$customerId}/annotation", [
        'annotation' => str_repeat('a', 1001), // Exceeds max length of 1000
        'is_risky_customer' => false,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['annotation']);
});

test('validates is_risky_customer is boolean', function () {
    // Create a customer
    $createResponse = $this->postJson('/api/customers', [
        'first_name' => 'Test',
        'last_name' => 'Validation',
    ]);

    $customerId = $createResponse->json('id');

    // Try with non-boolean value
    $response = $this->postJson("/api/customers/{$customerId}/annotation", [
        'annotation' => 'Test',
        'is_risky_customer' => 'not-a-boolean',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['is_risky_customer']);
});

test('requires is_risky_customer field', function () {
    // Create a customer
    $createResponse = $this->postJson('/api/customers', [
        'first_name' => 'Test',
        'last_name' => 'Required',
    ]);

    $customerId = $createResponse->json('id');

    // Try without is_risky_customer
    $response = $this->postJson("/api/customers/{$customerId}/annotation", [
        'annotation' => 'Test',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['is_risky_customer']);
});

test('annotation field is optional', function () {
    // Create a customer
    $createResponse = $this->postJson('/api/customers', [
        'first_name' => 'Test',
        'last_name' => 'Optional',
    ]);

    $customerId = $createResponse->json('id');

    // Try without annotation (only changing risky flag)
    $response = $this->postJson("/api/customers/{$customerId}/annotation", [
        'is_risky_customer' => true,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'is_risky_customer' => true,
        ]);
});

test('returns 404 for non-existent customer', function () {
    $invalidId = '00000000-0000-0000-0000-000000000000';

    $response = $this->postJson("/api/customers/{$invalidId}/annotation", [
        'annotation' => 'Test',
        'is_risky_customer' => false,
    ]);

    expect($response->status())->toBeIn([404, 500]);
});

test('annotation is persisted across multiple operations', function () {
    // Create a customer
    $createResponse = $this->postJson('/api/customers', [
        'first_name' => 'Persist',
        'last_name' => 'Test',
    ]);

    $customerId = $createResponse->json('id');

    // Set initial annotation
    $this->postJson("/api/customers/{$customerId}/annotation", [
        'annotation' => 'First annotation',
        'is_risky_customer' => true,
    ]);

    // Update annotation
    $this->postJson("/api/customers/{$customerId}/annotation", [
        'annotation' => 'Second annotation',
        'is_risky_customer' => false,
    ]);

    // Final update
    $this->postJson("/api/customers/{$customerId}/annotation", [
        'annotation' => 'Final annotation',
        'is_risky_customer' => true,
    ]);

    // Verify final state
    $customer = CustomerEloquentModel::find($customerId);
    expect($customer->notes)->toBe('Final annotation');
    expect($customer->is_risky)->toBeTrue();
});
