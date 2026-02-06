<?php

declare(strict_types=1);

use Fleet\Infrastructure\Persistence\Models\BikeEloquentModel;
use Fleet\Infrastructure\Persistence\Models\BrandEloquentModel;
use Fleet\Infrastructure\Persistence\Models\CategoryEloquentModel;
use Fleet\Infrastructure\Persistence\Models\ModelEloquentModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware();

    // Create necessary data for tests
    $this->brand = BrandEloquentModel::firstOrCreate(
        ['name' => 'Specialized'],
        ['logo_url' => 'https://example.com/logo.png']
    );

    $this->model = ModelEloquentModel::firstOrCreate(
        ['name' => 'Rockhopper', 'brand_id' => $this->brand->id]
    );

    $categoryId = Str::uuid()->toString();
    $this->category = CategoryEloquentModel::firstOrCreate(
        ['slug' => 'vtt'],
        [
            'id' => $categoryId,
            'name' => 'VTT',
            'description' => null,
            'is_default' => false,
            'display_order' => 0,
        ]
    );

    // Create a test bike
    $this->bike = BikeEloquentModel::create([
        'id' => Str::uuid()->toString(),
        'qr_code_uuid' => Str::uuid()->toString(),
        'internal_number' => 'VTT-STATUS-001',
        'model_id' => $this->model->id,
        'category_id' => $this->category->id,
        'frame_size_unit' => 'letter',
        'frame_size_letter' => 'm',
        'frame_size_letter_equivalent' => 'm',
        'status' => 'available',
        'pricing_tier' => 'standard',
    ]);
});

test('can change bike status to maintenance', function () {
    $response = $this->putJson("/api/fleet/bikes/{$this->bike->id}/status", [
        'status' => 'maintenance',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'id' => $this->bike->id,
            'message' => 'Bike status changed successfully',
        ]);

    $this->assertDatabaseHas('bikes', [
        'id' => $this->bike->id,
        'status' => 'maintenance',
    ]);
});

test('can change bike status to unavailable with reason reserved', function () {
    $response = $this->putJson("/api/fleet/bikes/{$this->bike->id}/status", [
        'status' => 'unavailable',
        'unavailability_reason' => 'reserved',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('bikes', [
        'id' => $this->bike->id,
        'status' => 'unavailable',
        'unavailability_reason' => 'reserved',
    ]);
});

test('can change status to unavailable with reason loaned', function () {
    $response = $this->putJson("/api/fleet/bikes/{$this->bike->id}/status", [
        'status' => 'unavailable',
        'unavailability_reason' => 'loaned',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('bikes', [
        'id' => $this->bike->id,
        'status' => 'unavailable',
        'unavailability_reason' => 'loaned',
    ]);
});

test('can change status to unavailable with reason other and comment', function () {
    $response = $this->putJson("/api/fleet/bikes/{$this->bike->id}/status", [
        'status' => 'unavailable',
        'unavailability_reason' => 'other',
        'unavailability_comment' => 'Waiting for VIP client',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('bikes', [
        'id' => $this->bike->id,
        'status' => 'unavailable',
        'unavailability_reason' => 'other',
        'unavailability_comment' => 'Waiting for VIP client',
    ]);
});

test('cannot change status to unavailable without reason', function () {
    $response = $this->putJson("/api/fleet/bikes/{$this->bike->id}/status", [
        'status' => 'unavailable',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['unavailability_reason']);
});

test('cannot change status when bike is rented', function () {
    $this->bike->update(['status' => 'rented']);

    $response = $this->putJson("/api/fleet/bikes/{$this->bike->id}/status", [
        'status' => 'maintenance',
    ]);

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Cannot manually change status of a rented bike',
        ]);
});

test('can change status from unavailable back to available', function () {
    $this->bike->update([
        'status' => 'unavailable',
        'unavailability_reason' => 'reserved',
        'unavailability_comment' => 'Test comment',
    ]);

    $response = $this->putJson("/api/fleet/bikes/{$this->bike->id}/status", [
        'status' => 'available',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('bikes', [
        'id' => $this->bike->id,
        'status' => 'available',
        'unavailability_reason' => null,
        'unavailability_comment' => null,
    ]);
});

test('can change status from maintenance to available', function () {
    $this->bike->update(['status' => 'maintenance']);

    $response = $this->putJson("/api/fleet/bikes/{$this->bike->id}/status", [
        'status' => 'available',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('bikes', [
        'id' => $this->bike->id,
        'status' => 'available',
    ]);
});

test('validates status is required', function () {
    $response = $this->putJson("/api/fleet/bikes/{$this->bike->id}/status", []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

test('validates status must be valid enum value', function () {
    $response = $this->putJson("/api/fleet/bikes/{$this->bike->id}/status", [
        'status' => 'invalid_status',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

test('cannot manually set status to rented', function () {
    $response = $this->putJson("/api/fleet/bikes/{$this->bike->id}/status", [
        'status' => 'rented',
    ]);

    // Validation fails at request level (422) before reaching domain logic
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

test('cannot manually set status to retired', function () {
    $response = $this->putJson("/api/fleet/bikes/{$this->bike->id}/status", [
        'status' => 'retired',
    ]);

    // Validation fails at request level (422) before reaching domain logic
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

test('validates unavailability reason must be valid when provided', function () {
    $response = $this->putJson("/api/fleet/bikes/{$this->bike->id}/status", [
        'status' => 'unavailable',
        'unavailability_reason' => 'invalid_reason',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['unavailability_reason']);
});

test('validates unavailability comment has max length', function () {
    $longComment = str_repeat('a', 1001);

    $response = $this->putJson("/api/fleet/bikes/{$this->bike->id}/status", [
        'status' => 'unavailable',
        'unavailability_reason' => 'other',
        'unavailability_comment' => $longComment,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['unavailability_comment']);
});

test('bike not found returns 404', function () {
    $invalidId = Str::uuid()->toString();

    $response = $this->putJson("/api/fleet/bikes/{$invalidId}/status", [
        'status' => 'maintenance',
    ]);

    $response->assertStatus(404);
});

test('status change history is recorded', function () {
    $response = $this->putJson("/api/fleet/bikes/{$this->bike->id}/status", [
        'status' => 'maintenance',
    ]);

    $response->assertStatus(200);

    // Verify history record was created
    $this->assertDatabaseHas('bike_status_history', [
        'bike_id' => $this->bike->id,
        'old_status' => 'available',
        'new_status' => 'maintenance',
    ]);
});

test('can retrieve bike status change history', function () {
    // Make several status changes
    $this->putJson("/api/fleet/bikes/{$this->bike->id}/status", [
        'status' => 'maintenance',
    ]);

    $this->putJson("/api/fleet/bikes/{$this->bike->id}/status", [
        'status' => 'available',
    ]);

    $this->putJson("/api/fleet/bikes/{$this->bike->id}/status", [
        'status' => 'unavailable',
        'unavailability_reason' => 'reserved',
    ]);

    // Get history
    $response = $this->getJson("/api/fleet/bikes/{$this->bike->id}/status-history");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'old_status',
                    'new_status',
                    'unavailability_reason',
                    'unavailability_comment',
                    'changed_at',
                ],
            ],
        ]);

    $history = $response->json('data');
    expect($history)->toHaveCount(3);
});
