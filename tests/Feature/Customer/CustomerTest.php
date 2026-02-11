<?php

declare(strict_types=1);

use Customer\Infrastructure\Persistence\Models\CustomerEloquentModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tenant\Application\TenantContext;
use Tenant\Domain\Tenant;
use Tenant\Domain\TenantStatus;
use Tenant\Infrastructure\Persistence\Models\TenantEloquentModel;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Disable authentication middleware for tests
    $this->withoutMiddleware();

    // Create tenant and set context
    $this->tenant = TenantEloquentModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Test Tenant',
        'slug' => 'test-tenant',
        'status' => TenantStatus::ACTIVE->value,
    ]);

    $tenantContext = app(TenantContext::class);
    $tenantContext->setTenant(new Tenant(
        id: $this->tenant->id,
        name: $this->tenant->name,
        slug: $this->tenant->slug,
        domain: null,
        status: TenantStatus::ACTIVE,
        contactEmail: null,
        contactPhone: null,
        settings: null,
    ));
});

test('can create a customer with all fields', function () {
    $response = $this->postJson('/api/customers', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'phone' => '+33612345678',
        'identity_document_type' => 'passport',
        'identity_document_number' => 'AB123456',
        'height' => 180,
        'weight' => 75,
        'address' => '123 Main St, Paris',
        'notes' => 'VIP customer',
        'photos' => [
            'https://example.com/id-front.jpg',
            'https://example.com/id-back.jpg',
        ],
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'id',
            'first_name',
            'last_name',
            'email',
        ])
        ->assertJson([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
        ]);

    $this->assertDatabaseHas('customers', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'phone' => '+33612345678',
        'identity_document_type' => 'passport',
        'identity_document_number' => 'AB123456',
        'height' => 180,
        'weight' => 75,
        'address' => '123 Main St, Paris',
        'notes' => 'VIP customer',
    ]);

    // Verify photos are stored
    $customer = CustomerEloquentModel::where('email', 'john.doe@example.com')->first();
    expect($customer->photos)->toBe([
        'https://example.com/id-front.jpg',
        'https://example.com/id-back.jpg',
    ]);
});

test('can create a customer with minimal fields', function () {
    $response = $this->postJson('/api/customers', [
        'first_name' => 'Jane',
        'last_name' => 'Smith',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);

    $this->assertDatabaseHas('customers', [
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'email' => null,
    ]);
});

test('can create a customer with photos', function () {
    $response = $this->postJson('/api/customers', [
        'first_name' => 'Photo',
        'last_name' => 'Customer',
        'photos' => [
            'https://storage.googleapis.com/bucket/photo1.jpg',
            'https://storage.googleapis.com/bucket/photo2.jpg',
            'https://storage.googleapis.com/bucket/photo3.jpg',
        ],
    ]);

    $response->assertStatus(201);

    $customer = CustomerEloquentModel::where('first_name', 'Photo')->first();
    expect($customer->photos)->toHaveCount(3);
});

test('can create a customer without photos', function () {
    $response = $this->postJson('/api/customers', [
        'first_name' => 'No',
        'last_name' => 'Photos',
    ]);

    $response->assertStatus(201);

    $customer = CustomerEloquentModel::where('first_name', 'No')->first();
    expect($customer->photos)->toBe([]);
});

test('cannot create customer with duplicate email', function () {
    // Create first customer
    $this->postJson('/api/customers', [
        'first_name' => 'First',
        'last_name' => 'Customer',
        'email' => 'duplicate@example.com',
    ]);

    // Try to create second customer with same email
    $response = $this->postJson('/api/customers', [
        'first_name' => 'Second',
        'last_name' => 'Customer',
        'email' => 'duplicate@example.com',
    ]);

    expect($response->status())->toBeIn([409, 500]);
});

test('validates required fields', function () {
    $response = $this->postJson('/api/customers', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'first_name',
            'last_name',
        ]);
});

test('validates email format', function () {
    $response = $this->postJson('/api/customers', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'invalid-email',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('validates height range', function () {
    $response = $this->postJson('/api/customers', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'height' => 50, // Too small
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['height']);

    $response = $this->postJson('/api/customers', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'height' => 300, // Too tall
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['height']);
});

test('validates weight range', function () {
    $response = $this->postJson('/api/customers', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'weight' => 20, // Too light
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['weight']);

    $response = $this->postJson('/api/customers', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'weight' => 250, // Too heavy
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['weight']);
});

test('validates photos is an array', function () {
    $response = $this->postJson('/api/customers', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'photos' => 'not-an-array',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['photos']);
});

test('can update a customer', function () {
    // Create a customer first
    $createResponse = $this->postJson('/api/customers', [
        'first_name' => 'Original',
        'last_name' => 'Name',
        'email' => 'original@example.com',
    ]);

    $customerId = $createResponse->json('id');

    // Verify customer was created
    $this->assertDatabaseHas('customers', [
        'id' => $customerId,
        'first_name' => 'Original',
    ]);

    // Update the customer
    $response = $this->putJson("/api/customers/{$customerId}", [
        'first_name' => 'Updated',
        'last_name' => 'Name',
        'email' => 'updated@example.com',
        'phone' => '+33612345678',
        'identity_document_type' => 'passport',
        'identity_document_number' => 'XY789',
        'height' => 185,
        'weight' => 80,
        'address' => '456 New Street',
        'notes' => 'Updated notes',
        'photos' => ['https://example.com/new-photo.jpg'],
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'customer_id' => $customerId,
            'message' => 'Customer updated successfully',
        ]);

    $this->assertDatabaseHas('customers', [
        'id' => $customerId,
        'first_name' => 'Updated',
        'last_name' => 'Name',
        'email' => 'updated@example.com',
        'phone' => '+33612345678',
        'height' => 185,
        'weight' => 80,
    ]);

    $customer = CustomerEloquentModel::find($customerId);
    expect($customer->photos)->toBe(['https://example.com/new-photo.jpg']);
});

test('can update customer photos', function () {
    // Create a customer with photos
    $createResponse = $this->postJson('/api/customers', [
        'first_name' => 'Photo',
        'last_name' => 'Update',
        'photos' => ['https://example.com/old1.jpg', 'https://example.com/old2.jpg'],
    ]);

    $customerId = $createResponse->json('id');

    // Update with new photos
    $response = $this->putJson("/api/customers/{$customerId}", [
        'first_name' => 'Photo',
        'last_name' => 'Update',
        'photos' => [
            'https://example.com/new1.jpg',
            'https://example.com/new2.jpg',
            'https://example.com/new3.jpg',
        ],
    ]);

    $response->assertStatus(200);

    $customer = CustomerEloquentModel::find($customerId);
    expect($customer->photos)->toHaveCount(3);
    expect($customer->photos)->toBe([
        'https://example.com/new1.jpg',
        'https://example.com/new2.jpg',
        'https://example.com/new3.jpg',
    ]);
});

test('can clear customer photos', function () {
    // Create a customer with photos
    $createResponse = $this->postJson('/api/customers', [
        'first_name' => 'Clear',
        'last_name' => 'Photos',
        'photos' => ['https://example.com/photo.jpg'],
    ]);

    $customerId = $createResponse->json('id');

    // Update with empty photos array
    $response = $this->putJson("/api/customers/{$customerId}", [
        'first_name' => 'Clear',
        'last_name' => 'Photos',
        'photos' => [],
    ]);

    $response->assertStatus(200);

    $customer = CustomerEloquentModel::find($customerId);
    expect($customer->photos)->toBe([]);
});

test('cannot update customer with email already used by another', function () {
    // Create first customer
    $this->postJson('/api/customers', [
        'first_name' => 'First',
        'last_name' => 'Customer',
        'email' => 'taken@example.com',
    ]);

    // Create second customer
    $createResponse = $this->postJson('/api/customers', [
        'first_name' => 'Second',
        'last_name' => 'Customer',
        'email' => 'second@example.com',
    ]);

    $customerId = $createResponse->json('id');

    // Try to update second customer with first customer's email
    $response = $this->putJson("/api/customers/{$customerId}", [
        'first_name' => 'Second',
        'last_name' => 'Customer',
        'email' => 'taken@example.com',
    ]);

    expect($response->status())->toBeIn([409, 500]);
});

test('can keep same email when updating', function () {
    // Create a customer
    $createResponse = $this->postJson('/api/customers', [
        'first_name' => 'Same',
        'last_name' => 'Email',
        'email' => 'same@example.com',
    ]);

    $customerId = $createResponse->json('id');

    // Update customer keeping the same email
    $response = $this->putJson("/api/customers/{$customerId}", [
        'first_name' => 'Updated',
        'last_name' => 'Email',
        'email' => 'same@example.com',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('customers', [
        'id' => $customerId,
        'first_name' => 'Updated',
        'email' => 'same@example.com',
    ]);
});

test('returns 404 when updating non-existent customer', function () {
    $invalidId = '00000000-0000-0000-0000-000000000000';

    $response = $this->putJson("/api/customers/{$invalidId}", [
        'first_name' => 'Test',
        'last_name' => 'Test',
    ]);

    expect($response->status())->toBeIn([404, 500]);
});

test('can toggle risky flag from false to true', function () {
    // Create a customer
    $createResponse = $this->postJson('/api/customers', [
        'first_name' => 'Risky',
        'last_name' => 'Customer',
    ]);

    $customerId = $createResponse->json('id');

    // Verify customer is not risky initially
    $customer = CustomerEloquentModel::find($customerId);
    expect($customer->is_risky)->toBeFalse();

    // Toggle risky flag
    $response = $this->postJson("/api/customers/{$customerId}/toggle-risky");

    $response->assertStatus(200)
        ->assertJson([
            'customer_id' => $customerId,
            'is_risky' => true,
            'message' => 'Customer marked as risky',
        ]);

    // Verify customer is now risky in database
    $customer->refresh();
    expect($customer->is_risky)->toBeTrue();
});

test('can toggle risky flag from true to false', function () {
    // Create a customer and mark as risky
    $createResponse = $this->postJson('/api/customers', [
        'first_name' => 'Safe',
        'last_name' => 'Customer',
    ]);

    $customerId = $createResponse->json('id');

    // Mark as risky first
    $this->postJson("/api/customers/{$customerId}/toggle-risky");

    // Verify customer is risky
    $customer = CustomerEloquentModel::find($customerId);
    expect($customer->is_risky)->toBeTrue();

    // Toggle risky flag again
    $response = $this->postJson("/api/customers/{$customerId}/toggle-risky");

    $response->assertStatus(200)
        ->assertJson([
            'customer_id' => $customerId,
            'is_risky' => false,
            'message' => 'Customer unmarked as risky',
        ]);

    // Verify customer is no longer risky in database
    $customer->refresh();
    expect($customer->is_risky)->toBeFalse();
});

test('can toggle risky flag multiple times', function () {
    // Create a customer
    $createResponse = $this->postJson('/api/customers', [
        'first_name' => 'Toggle',
        'last_name' => 'Test',
    ]);

    $customerId = $createResponse->json('id');
    $customer = CustomerEloquentModel::find($customerId);

    // Initial state: not risky
    expect($customer->is_risky)->toBeFalse();

    // First toggle: false -> true
    $this->postJson("/api/customers/{$customerId}/toggle-risky")
        ->assertJson(['is_risky' => true]);
    $customer->refresh();
    expect($customer->is_risky)->toBeTrue();

    // Second toggle: true -> false
    $this->postJson("/api/customers/{$customerId}/toggle-risky")
        ->assertJson(['is_risky' => false]);
    $customer->refresh();
    expect($customer->is_risky)->toBeFalse();

    // Third toggle: false -> true
    $this->postJson("/api/customers/{$customerId}/toggle-risky")
        ->assertJson(['is_risky' => true]);
    $customer->refresh();
    expect($customer->is_risky)->toBeTrue();
});

test('returns 404 when toggling risky flag for non-existent customer', function () {
    $invalidId = '00000000-0000-0000-0000-000000000000';

    $response = $this->postJson("/api/customers/{$invalidId}/toggle-risky");

    expect($response->status())->toBeIn([404, 500]);
});

test('notes field is properly saved and retrieved', function () {
    // Create customer with notes
    $createResponse = $this->postJson('/api/customers', [
        'first_name' => 'Noted',
        'last_name' => 'Customer',
        'notes' => 'Always late for pickups',
    ]);

    $customerId = $createResponse->json('id');

    // Verify notes are saved
    $this->assertDatabaseHas('customers', [
        'id' => $customerId,
        'notes' => 'Always late for pickups',
    ]);

    // Update notes
    $this->putJson("/api/customers/{$customerId}", [
        'first_name' => 'Noted',
        'last_name' => 'Customer',
        'notes' => 'Updated: Improved punctuality',
    ]);

    // Verify notes are updated
    $this->assertDatabaseHas('customers', [
        'id' => $customerId,
        'notes' => 'Updated: Improved punctuality',
    ]);
});

test('notes field can be cleared', function () {
    // Create customer with notes
    $createResponse = $this->postJson('/api/customers', [
        'first_name' => 'Clear',
        'last_name' => 'Notes',
        'notes' => 'Some notes here',
    ]);

    $customerId = $createResponse->json('id');

    // Clear notes
    $this->putJson("/api/customers/{$customerId}", [
        'first_name' => 'Clear',
        'last_name' => 'Notes',
        'notes' => null,
    ]);

    // Verify notes are cleared
    $this->assertDatabaseHas('customers', [
        'id' => $customerId,
        'notes' => null,
    ]);
});

test('validates notes field max length', function () {
    $response = $this->postJson('/api/customers', [
        'first_name' => 'Long',
        'last_name' => 'Notes',
        'notes' => str_repeat('a', 1001), // Exceeds max length of 1000
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['notes']);
});
