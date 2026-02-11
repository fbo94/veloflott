<?php

declare(strict_types=1);

use Fleet\Infrastructure\Persistence\Models\BikeEloquentModel;
use Fleet\Infrastructure\Persistence\Models\BrandEloquentModel;
use Fleet\Infrastructure\Persistence\Models\CategoryEloquentModel;
use Fleet\Infrastructure\Persistence\Models\ModelEloquentModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tenant\Application\TenantContext;
use Tenant\Domain\Tenant;
use Tenant\Domain\TenantStatus;
use Tenant\Infrastructure\Persistence\Models\TenantEloquentModel;

uses(RefreshDatabase::class);

beforeEach(function () {
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

    // Create necessary data for tests
    $this->brand = BrandEloquentModel::firstOrCreate(
        ['name' => 'Trek'],
        ['logo_url' => 'https://example.com/logo.png']
    );

    $this->model = ModelEloquentModel::firstOrCreate(
        ['name' => 'Marlin 7', 'brand_id' => $this->brand->id]
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
        'internal_number' => 'VTT-RETIRE-001',
        'model_id' => $this->model->id,
        'category_id' => $this->category->id,
        'frame_size_unit' => 'letter',
        'frame_size_letter' => 'm',
        'frame_size_letter_equivalent' => 'm',
        'status' => 'available',
        'pricing_tier' => 'standard',
    ]);
});

test('can retire a bike with reason sold', function () {
    $response = $this->postJson("/api/fleet/bikes/{$this->bike->id}/retire", [
        'reason' => 'sold',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'id' => $this->bike->id,
            'message' => 'Bike retired successfully',
        ]);

    $this->assertDatabaseHas('bikes', [
        'id' => $this->bike->id,
        'status' => 'retired',
        'retirement_reason' => 'sold',
    ]);

    // Verify retired_at is set
    $bike = BikeEloquentModel::find($this->bike->id);
    expect($bike->retired_at)->not->toBeNull();
});

test('can retire a bike with reason stolen', function () {
    $response = $this->postJson("/api/fleet/bikes/{$this->bike->id}/retire", [
        'reason' => 'stolen',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('bikes', [
        'id' => $this->bike->id,
        'status' => 'retired',
        'retirement_reason' => 'stolen',
    ]);
});

test('can retire a bike with reason permanently out of service', function () {
    $response = $this->postJson("/api/fleet/bikes/{$this->bike->id}/retire", [
        'reason' => 'permanently_out_of_service',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('bikes', [
        'id' => $this->bike->id,
        'status' => 'retired',
        'retirement_reason' => 'permanently_out_of_service',
    ]);
});

test('can retire a bike with reason other and comment', function () {
    $response = $this->postJson("/api/fleet/bikes/{$this->bike->id}/retire", [
        'reason' => 'other',
        'comment' => 'Donated to local charity',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('bikes', [
        'id' => $this->bike->id,
        'status' => 'retired',
        'retirement_reason' => 'other',
        'retirement_comment' => 'Donated to local charity',
    ]);
});

test('cannot retire a bike that is currently rented', function () {
    // Set bike status to rented
    $this->bike->update(['status' => 'rented']);

    $response = $this->postJson("/api/fleet/bikes/{$this->bike->id}/retire", [
        'reason' => 'sold',
    ]);

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Cannot retire a bike that is currently rented',
        ]);

    // Verify bike is still rented, not retired
    $this->assertDatabaseHas('bikes', [
        'id' => $this->bike->id,
        'status' => 'rented',
    ]);
});

test('cannot retire a bike with non-existent ID', function () {
    $invalidId = Str::uuid()->toString();

    $response = $this->postJson("/api/fleet/bikes/{$invalidId}/retire", [
        'reason' => 'sold',
    ]);

    $response->assertStatus(404);
});

test('validates reason is required', function () {
    $response = $this->postJson("/api/fleet/bikes/{$this->bike->id}/retire", []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['reason']);
});

test('validates reason must be valid enum value', function () {
    $response = $this->postJson("/api/fleet/bikes/{$this->bike->id}/retire", [
        'reason' => 'invalid_reason',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['reason']);
});

test('validates comment has max length', function () {
    $longComment = str_repeat('a', 1001);

    $response = $this->postJson("/api/fleet/bikes/{$this->bike->id}/retire", [
        'reason' => 'other',
        'comment' => $longComment,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['comment']);
});

test('retired bikes are not included in default bike list', function () {
    // Retire the bike
    $this->postJson("/api/fleet/bikes/{$this->bike->id}/retire", [
        'reason' => 'sold',
    ]);

    // Get bikes list (default excludes retired)
    $response = $this->getJson('/api/fleet/bikes');

    $response->assertStatus(200);

    $bikes = $response->json('data');
    $bikeIds = array_column($bikes, 'id');

    expect($bikeIds)->not->toContain($this->bike->id);
});

test('retired bikes can be included when filter is set', function () {
    // Retire the bike
    $this->postJson("/api/fleet/bikes/{$this->bike->id}/retire", [
        'reason' => 'sold',
    ]);

    // Get bikes list with include_retired=true
    $response = $this->getJson('/api/fleet/bikes?include_retired=true');

    $response->assertStatus(200);

    $bikes = $response->json('data');
    $bikeIds = array_column($bikes, 'id');

    expect($bikeIds)->toContain($this->bike->id);
});

test('can retire a bike in maintenance status', function () {
    $this->bike->update(['status' => 'maintenance']);

    $response = $this->postJson("/api/fleet/bikes/{$this->bike->id}/retire", [
        'reason' => 'permanently_out_of_service',
        'comment' => 'Too expensive to repair',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('bikes', [
        'id' => $this->bike->id,
        'status' => 'retired',
        'retirement_reason' => 'permanently_out_of_service',
        'retirement_comment' => 'Too expensive to repair',
    ]);
});

test('can retire a bike in unavailable status', function () {
    $this->bike->update(['status' => 'unavailable']);

    $response = $this->postJson("/api/fleet/bikes/{$this->bike->id}/retire", [
        'reason' => 'sold',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('bikes', [
        'id' => $this->bike->id,
        'status' => 'retired',
    ]);
});
