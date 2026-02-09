<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Tenant\Application\TenantContext;
use Tenant\Domain\Site;
use Tenant\Domain\SiteStatus;
use Tenant\Domain\Tenant;
use Tenant\Domain\TenantStatus;
use Tenant\Infrastructure\Persistence\Models\SiteEloquentModel;
use Tenant\Infrastructure\Persistence\Models\TenantEloquentModel;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Disable authentication middleware for tests
    $this->withoutMiddleware();

    // Generate a valid UUID for tenant
    $this->tenantId = Uuid::uuid4()->toString();

    // Create a tenant
    $this->tenant = TenantEloquentModel::create([
        'id' => $this->tenantId,
        'name' => 'Test Tenant',
        'slug' => 'test-tenant',
        'domain' => null,
        'status' => TenantStatus::ACTIVE->value,
        'contact_email' => 'contact@test.com',
        'contact_phone' => null,
        'settings' => null,
    ]);

    // Setup TenantContext
    $tenantContext = app(TenantContext::class);
    $tenantContext->setTenant(new Tenant(
        id: $this->tenantId,
        name: 'Test Tenant',
        slug: 'test-tenant',
        domain: null,
        status: TenantStatus::ACTIVE,
        contactEmail: 'contact@test.com',
        contactPhone: null,
        settings: null,
    ));
});

test('can list sites for current tenant', function () {
    // Create sites for tenant
    SiteEloquentModel::create([
        'id' => Uuid::uuid4()->toString(),
        'tenant_id' => $this->tenantId,
        'name' => 'Paris 11ème',
        'slug' => 'paris-11',
        'address' => '15 Rue de la Roquette',
        'city' => 'Paris',
        'postal_code' => '75011',
        'country' => 'FR',
        'phone' => null,
        'email' => null,
        'status' => SiteStatus::ACTIVE->value,
        'opening_hours' => null,
        'settings' => null,
        'latitude' => null,
        'longitude' => null,
    ]);

    SiteEloquentModel::create([
        'id' => Uuid::uuid4()->toString(),
        'tenant_id' => $this->tenantId,
        'name' => 'Paris 15ème',
        'slug' => 'paris-15',
        'address' => null,
        'city' => 'Paris',
        'postal_code' => '75015',
        'country' => 'FR',
        'phone' => null,
        'email' => null,
        'status' => SiteStatus::ACTIVE->value,
        'opening_hours' => null,
        'settings' => null,
        'latitude' => null,
        'longitude' => null,
    ]);

    $response = $this->getJson('/api/sites');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.name', 'Paris 11ème')
        ->assertJsonPath('data.1.name', 'Paris 15ème');
});

test('can create a site', function () {
    $response = $this->postJson('/api/sites', [
        'name' => 'Lyon Presqu\'île',
        'slug' => 'lyon-presquile',
        'address' => '10 Place Bellecour',
        'city' => 'Lyon',
        'postal_code' => '69002',
        'country' => 'FR',
        'phone' => '+33478123456',
        'email' => 'lyon@bikes.com',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'site' => [
                'id',
                'name',
                'slug',
                'status',
            ],
        ])
        ->assertJsonPath('site.name', 'Lyon Presqu\'île')
        ->assertJsonPath('site.slug', 'lyon-presquile')
        ->assertJsonPath('site.status', 'active');

    $this->assertDatabaseHas('sites', [
        'tenant_id' => $this->tenantId,
        'name' => 'Lyon Presqu\'île',
        'slug' => 'lyon-presquile',
        'city' => 'Lyon',
    ]);
});

test('can get site details', function () {
    $siteId = Uuid::uuid4()->toString();
    SiteEloquentModel::create([
        'id' => $siteId,
        'tenant_id' => $this->tenantId,
        'name' => 'Detail Site',
        'slug' => 'detail-site',
        'address' => '123 Test Street',
        'city' => 'Marseille',
        'postal_code' => '13001',
        'country' => 'FR',
        'phone' => '+33491123456',
        'email' => 'marseille@bikes.com',
        'status' => SiteStatus::ACTIVE->value,
        'opening_hours' => ['monday' => ['open' => '09:00', 'close' => '19:00']],
        'settings' => ['max_rentals' => 50],
        'latitude' => 43.2965,
        'longitude' => 5.3698,
    ]);

    $response = $this->getJson("/api/sites/{$siteId}");

    $response->assertStatus(200)
        ->assertJsonPath('name', 'Detail Site')
        ->assertJsonPath('city', 'Marseille')
        ->assertJsonPath('phone', '+33491123456')
        ->assertJsonPath('latitude', 43.2965);
});

test('can update a site', function () {
    $siteId = Uuid::uuid4()->toString();
    SiteEloquentModel::create([
        'id' => $siteId,
        'tenant_id' => $this->tenantId,
        'name' => 'Original Name',
        'slug' => 'original-name',
        'address' => null,
        'city' => null,
        'postal_code' => null,
        'country' => 'FR',
        'phone' => null,
        'email' => null,
        'status' => SiteStatus::ACTIVE->value,
        'opening_hours' => null,
        'settings' => null,
        'latitude' => null,
        'longitude' => null,
    ]);

    $response = $this->putJson("/api/sites/{$siteId}", [
        'name' => 'Updated Name',
        'address' => '456 New Street',
        'city' => 'Nice',
        'postal_code' => '06000',
        'country' => 'FR',
        'phone' => '+33493123456',
        'email' => 'nice@bikes.com',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('message', 'Site updated successfully');

    $this->assertDatabaseHas('sites', [
        'id' => $siteId,
        'name' => 'Updated Name',
        'city' => 'Nice',
        'phone' => '+33493123456',
    ]);
});

test('can change site status', function () {
    $siteId = Uuid::uuid4()->toString();
    SiteEloquentModel::create([
        'id' => $siteId,
        'tenant_id' => $this->tenantId,
        'name' => 'Status Site',
        'slug' => 'status-site',
        'address' => null,
        'city' => null,
        'postal_code' => null,
        'country' => 'FR',
        'phone' => null,
        'email' => null,
        'status' => SiteStatus::ACTIVE->value,
        'opening_hours' => null,
        'settings' => null,
        'latitude' => null,
        'longitude' => null,
    ]);

    $response = $this->putJson("/api/sites/{$siteId}/status", [
        'status' => 'suspended',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('site.status', 'suspended');

    $this->assertDatabaseHas('sites', [
        'id' => $siteId,
        'status' => SiteStatus::SUSPENDED->value,
    ]);
});

test('can delete a site', function () {
    $siteId = Uuid::uuid4()->toString();
    SiteEloquentModel::create([
        'id' => $siteId,
        'tenant_id' => $this->tenantId,
        'name' => 'Delete Site',
        'slug' => 'delete-site',
        'address' => null,
        'city' => null,
        'postal_code' => null,
        'country' => 'FR',
        'phone' => null,
        'email' => null,
        'status' => SiteStatus::CLOSED->value, // Site must be closed or suspended to delete
        'opening_hours' => null,
        'settings' => null,
        'latitude' => null,
        'longitude' => null,
    ]);

    $response = $this->deleteJson("/api/sites/{$siteId}");

    $response->assertStatus(204);

    $this->assertDatabaseMissing('sites', [
        'id' => $siteId,
    ]);
});

test('cannot create site with duplicate slug', function () {
    SiteEloquentModel::create([
        'id' => Uuid::uuid4()->toString(),
        'tenant_id' => $this->tenantId,
        'name' => 'Existing Site',
        'slug' => 'existing-slug',
        'address' => null,
        'city' => null,
        'postal_code' => null,
        'country' => 'FR',
        'phone' => null,
        'email' => null,
        'status' => SiteStatus::ACTIVE->value,
        'opening_hours' => null,
        'settings' => null,
        'latitude' => null,
        'longitude' => null,
    ]);

    $response = $this->postJson('/api/sites', [
        'name' => 'Another Site',
        'slug' => 'existing-slug',
    ]);

    expect($response->status())->toBeIn([400, 409, 422, 500]);
});

test('returns 404 for non-existent site', function () {
    $nonExistentId = Uuid::uuid4()->toString();
    $response = $this->getJson("/api/sites/{$nonExistentId}");

    $response->assertStatus(404);
});

test('validates required fields on create', function () {
    $response = $this->postJson('/api/sites', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'slug']);
});

test('can filter sites by status', function () {
    SiteEloquentModel::create([
        'id' => Uuid::uuid4()->toString(),
        'tenant_id' => $this->tenantId,
        'name' => 'Active Site',
        'slug' => 'active-site',
        'address' => null,
        'city' => null,
        'postal_code' => null,
        'country' => 'FR',
        'phone' => null,
        'email' => null,
        'status' => SiteStatus::ACTIVE->value,
        'opening_hours' => null,
        'settings' => null,
        'latitude' => null,
        'longitude' => null,
    ]);

    SiteEloquentModel::create([
        'id' => Uuid::uuid4()->toString(),
        'tenant_id' => $this->tenantId,
        'name' => 'Suspended Site',
        'slug' => 'suspended-site',
        'address' => null,
        'city' => null,
        'postal_code' => null,
        'country' => 'FR',
        'phone' => null,
        'email' => null,
        'status' => SiteStatus::SUSPENDED->value,
        'opening_hours' => null,
        'settings' => null,
        'latitude' => null,
        'longitude' => null,
    ]);

    $response = $this->getJson('/api/sites?active_only=true');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Active Site');
});
