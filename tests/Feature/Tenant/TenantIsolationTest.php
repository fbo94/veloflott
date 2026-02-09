<?php

declare(strict_types=1);

use Customer\Infrastructure\Persistence\Models\CustomerEloquentModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tenant\Application\TenantContext;
use Tenant\Domain\SiteStatus;
use Tenant\Domain\Tenant;
use Tenant\Domain\TenantStatus;
use Tenant\Infrastructure\Persistence\Models\SiteEloquentModel;
use Tenant\Infrastructure\Persistence\Models\TenantEloquentModel;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Disable authentication middleware for tests
    $this->withoutMiddleware();

    // Create two tenants
    $this->tenant1 = TenantEloquentModel::create([
        'id' => 'tenant-1',
        'name' => 'Tenant One',
        'slug' => 'tenant-one',
        'domain' => null,
        'status' => TenantStatus::ACTIVE->value,
        'contact_email' => null,
        'contact_phone' => null,
        'settings' => null,
    ]);

    $this->tenant2 = TenantEloquentModel::create([
        'id' => 'tenant-2',
        'name' => 'Tenant Two',
        'slug' => 'tenant-two',
        'domain' => null,
        'status' => TenantStatus::ACTIVE->value,
        'contact_email' => null,
        'contact_phone' => null,
        'settings' => null,
    ]);

    // Create sites for each tenant
    SiteEloquentModel::create([
        'id' => 'site-t1-1',
        'tenant_id' => 'tenant-1',
        'name' => 'Tenant 1 - Site 1',
        'slug' => 'site-1',
        'address' => null,
        'city' => 'Paris',
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
        'id' => 'site-t1-2',
        'tenant_id' => 'tenant-1',
        'name' => 'Tenant 1 - Site 2',
        'slug' => 'site-2',
        'address' => null,
        'city' => 'Lyon',
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
        'id' => 'site-t2-1',
        'tenant_id' => 'tenant-2',
        'name' => 'Tenant 2 - Site 1',
        'slug' => 'site-1',
        'address' => null,
        'city' => 'Marseille',
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

    $this->tenantContext = app(TenantContext::class);
});

test('sites are isolated by tenant - tenant 1 sees only their sites', function () {
    // Set context to tenant 1
    $this->tenantContext->setTenant(new Tenant(
        id: 'tenant-1',
        name: 'Tenant One',
        slug: 'tenant-one',
        domain: null,
        status: TenantStatus::ACTIVE,
        contactEmail: null,
        contactPhone: null,
        settings: null,
    ));

    $response = $this->getJson('/api/sites');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');

    $siteNames = collect($response->json('data'))->pluck('name')->toArray();
    expect($siteNames)->toContain('Tenant 1 - Site 1');
    expect($siteNames)->toContain('Tenant 1 - Site 2');
    expect($siteNames)->not->toContain('Tenant 2 - Site 1');
});

test('sites are isolated by tenant - tenant 2 sees only their sites', function () {
    // Set context to tenant 2
    $this->tenantContext->setTenant(new Tenant(
        id: 'tenant-2',
        name: 'Tenant Two',
        slug: 'tenant-two',
        domain: null,
        status: TenantStatus::ACTIVE,
        contactEmail: null,
        contactPhone: null,
        settings: null,
    ));

    $response = $this->getJson('/api/sites');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Tenant 2 - Site 1');
});

test('same slug can exist for different tenants', function () {
    // Both tenants have a site with slug 'site-1'
    $this->assertDatabaseHas('sites', [
        'tenant_id' => 'tenant-1',
        'slug' => 'site-1',
    ]);

    $this->assertDatabaseHas('sites', [
        'tenant_id' => 'tenant-2',
        'slug' => 'site-1',
    ]);
});

test('tenant 1 cannot access tenant 2 site by id', function () {
    // Set context to tenant 1
    $this->tenantContext->setTenant(new Tenant(
        id: 'tenant-1',
        name: 'Tenant One',
        slug: 'tenant-one',
        domain: null,
        status: TenantStatus::ACTIVE,
        contactEmail: null,
        contactPhone: null,
        settings: null,
    ));

    // Try to access tenant 2's site
    $response = $this->getJson('/api/sites/site-t2-1');

    // Should return 404 because site belongs to different tenant
    $response->assertStatus(404);
});

test('tenant 2 cannot access tenant 1 sites by id', function () {
    // Set context to tenant 2
    $this->tenantContext->setTenant(new Tenant(
        id: 'tenant-2',
        name: 'Tenant Two',
        slug: 'tenant-two',
        domain: null,
        status: TenantStatus::ACTIVE,
        contactEmail: null,
        contactPhone: null,
        settings: null,
    ));

    // Try to access tenant 1's sites
    $response1 = $this->getJson('/api/sites/site-t1-1');
    $response2 = $this->getJson('/api/sites/site-t1-2');

    $response1->assertStatus(404);
    $response2->assertStatus(404);
});

test('new sites are automatically assigned to current tenant', function () {
    // Set context to tenant 1
    $this->tenantContext->setTenant(new Tenant(
        id: 'tenant-1',
        name: 'Tenant One',
        slug: 'tenant-one',
        domain: null,
        status: TenantStatus::ACTIVE,
        contactEmail: null,
        contactPhone: null,
        settings: null,
    ));

    $response = $this->postJson('/api/sites', [
        'name' => 'New Tenant 1 Site',
        'slug' => 'new-site',
    ]);

    $response->assertStatus(201);

    $siteId = $response->json('site.id');
    $this->assertDatabaseHas('sites', [
        'id' => $siteId,
        'tenant_id' => 'tenant-1',
        'name' => 'New Tenant 1 Site',
    ]);
});

test('TenantScoped trait filters data automatically', function () {
    // Without tenant context, direct query should return all
    $allSites = SiteEloquentModel::withoutGlobalScopes()->get();
    expect($allSites)->toHaveCount(3);

    // With tenant context, query should be filtered
    $this->tenantContext->setTenant(new Tenant(
        id: 'tenant-1',
        name: 'Tenant One',
        slug: 'tenant-one',
        domain: null,
        status: TenantStatus::ACTIVE,
        contactEmail: null,
        contactPhone: null,
        settings: null,
    ));

    // This should only return tenant 1's sites
    $tenant1Sites = SiteEloquentModel::all();
    expect($tenant1Sites)->toHaveCount(2);
    foreach ($tenant1Sites as $site) {
        expect($site->tenant_id)->toBe('tenant-1');
    }
});

test('tenant cannot modify another tenant site', function () {
    // Set context to tenant 1
    $this->tenantContext->setTenant(new Tenant(
        id: 'tenant-1',
        name: 'Tenant One',
        slug: 'tenant-one',
        domain: null,
        status: TenantStatus::ACTIVE,
        contactEmail: null,
        contactPhone: null,
        settings: null,
    ));

    // Try to update tenant 2's site
    $response = $this->putJson('/api/sites/site-t2-1', [
        'name' => 'Hacked Name',
    ]);

    // Should return 404 because site not visible to current tenant
    $response->assertStatus(404);

    // Verify data unchanged
    $this->assertDatabaseHas('sites', [
        'id' => 'site-t2-1',
        'name' => 'Tenant 2 - Site 1',
    ]);
});

test('tenant cannot delete another tenant site', function () {
    // Set context to tenant 1
    $this->tenantContext->setTenant(new Tenant(
        id: 'tenant-1',
        name: 'Tenant One',
        slug: 'tenant-one',
        domain: null,
        status: TenantStatus::ACTIVE,
        contactEmail: null,
        contactPhone: null,
        settings: null,
    ));

    // Try to delete tenant 2's site
    $response = $this->deleteJson('/api/sites/site-t2-1');

    // Should return 404 because site not visible
    $response->assertStatus(404);

    // Verify site still exists
    $this->assertDatabaseHas('sites', [
        'id' => 'site-t2-1',
    ]);
});
