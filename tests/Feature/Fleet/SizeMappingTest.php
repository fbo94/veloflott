<?php

declare(strict_types=1);

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

    // Default sizes payload for updates
    $this->defaultSizes = [
        ['letter' => 'xs', 'label' => 'XS', 'cm' => ['min' => 48, 'max' => 50], 'inch' => ['min' => 13, 'max' => 14]],
        ['letter' => 's', 'label' => 'S', 'cm' => ['min' => 51, 'max' => 53], 'inch' => ['min' => 15, 'max' => 16]],
        ['letter' => 'm', 'label' => 'M', 'cm' => ['min' => 54, 'max' => 56], 'inch' => ['min' => 17, 'max' => 18]],
        ['letter' => 'l', 'label' => 'L', 'cm' => ['min' => 57, 'max' => 59], 'inch' => ['min' => 19, 'max' => 20]],
        ['letter' => 'xl', 'label' => 'XL', 'cm' => ['min' => 60, 'max' => 62], 'inch' => ['min' => 21, 'max' => 22]],
        ['letter' => 'xxl', 'label' => 'XXL', 'cm' => ['min' => 63, 'max' => 999], 'inch' => ['min' => 23, 'max' => 999]],
    ];

    $this->customSizes = [
        ['letter' => 'xs', 'label' => 'XS', 'cm' => ['min' => 45, 'max' => 48], 'inch' => ['min' => 12, 'max' => 13]],
        ['letter' => 's', 'label' => 'S', 'cm' => ['min' => 49, 'max' => 52], 'inch' => ['min' => 14, 'max' => 15]],
        ['letter' => 'm', 'label' => 'M', 'cm' => ['min' => 53, 'max' => 56], 'inch' => ['min' => 16, 'max' => 18]],
        ['letter' => 'l', 'label' => 'L', 'cm' => ['min' => 57, 'max' => 60], 'inch' => ['min' => 19, 'max' => 21]],
        ['letter' => 'xl', 'label' => 'XL', 'cm' => ['min' => 61, 'max' => 64], 'inch' => ['min' => 22, 'max' => 24]],
        ['letter' => 'xxl', 'label' => 'XXL', 'cm' => ['min' => 65, 'max' => 999], 'inch' => ['min' => 25, 'max' => 999]],
    ];
});

test('can get active size mapping configuration', function () {
    $response = $this->getJson('/api/fleet/size-mapping');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'id',
            'version',
            'is_active',
            'sizes' => [
                '*' => ['letter', 'label', 'cm' => ['min', 'max'], 'inch' => ['min', 'max']],
            ],
            'created_at',
            'updated_at',
        ])
        ->assertJson([
            'version' => 1,
            'is_active' => true,
        ]);
});

test('can update size mapping configuration', function () {
    $response = $this->putJson('/api/fleet/size-mapping', [
        'sizes' => $this->customSizes,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'version' => 2,
            'is_active' => true,
        ]);

    // Verify updated values in response
    $sizes = $response->json('sizes');
    expect($sizes[0])->toMatchArray(['letter' => 'xs', 'cm' => ['min' => 45, 'max' => 48]]);
    expect($sizes[1])->toMatchArray(['letter' => 's', 'cm' => ['min' => 49, 'max' => 52]]);

    // Verify in database that old version is deactivated
    $this->assertDatabaseHas('size_mapping_configurations', [
        'version' => 1,
        'is_active' => false,
    ]);

    $this->assertDatabaseHas('size_mapping_configurations', [
        'version' => 2,
        'is_active' => true,
    ]);
});

test('validates min cannot be greater than max in cm ranges', function () {
    $invalidSizes = $this->customSizes;
    $invalidSizes[0]['cm'] = ['min' => 50, 'max' => 48]; // Invalid: min > max

    $response = $this->putJson('/api/fleet/size-mapping', [
        'sizes' => $invalidSizes,
    ]);

    $response->assertStatus(422);
});

test('validates min cannot be greater than max in inch ranges', function () {
    $invalidSizes = $this->customSizes;
    $invalidSizes[0]['inch'] = ['min' => 14, 'max' => 12]; // Invalid: min > max

    $response = $this->putJson('/api/fleet/size-mapping', [
        'sizes' => $invalidSizes,
    ]);

    $response->assertStatus(422);
});

test('validates all size ranges are required', function () {
    $response = $this->putJson('/api/fleet/size-mapping', [
        'sizes' => [
            ['letter' => 'xs', 'label' => 'XS', 'cm' => ['min' => 45, 'max' => 48], 'inch' => ['min' => 12, 'max' => 13]],
            // Missing other 5 sizes
        ],
    ]);

    $response->assertStatus(422);
});

test('validates range values must be positive', function () {
    $invalidSizes = $this->customSizes;
    $invalidSizes[0]['cm'] = ['min' => -5, 'max' => 48]; // Invalid: negative value

    $response = $this->putJson('/api/fleet/size-mapping', [
        'sizes' => $invalidSizes,
    ]);

    $response->assertStatus(422);
});

test('can reset size mapping to default values', function () {
    // First update to custom values
    $this->putJson('/api/fleet/size-mapping', [
        'sizes' => $this->customSizes,
    ]);

    // Reset to defaults
    $response = $this->postJson('/api/fleet/size-mapping/reset');

    $response->assertStatus(200)
        ->assertJson([
            'version' => 3,
            'is_active' => true,
        ]);

    // Verify sizes are reset to defaults
    $sizes = $response->json('sizes');
    expect($sizes[0]['cm']['min'])->toBe(48);
    expect($sizes[0]['cm']['max'])->toBe(50);

    // Verify previous version is deactivated
    $this->assertDatabaseHas('size_mapping_configurations', [
        'version' => 2,
        'is_active' => false,
    ]);
});

test('version numbers increment correctly', function () {
    // Get initial version
    $response1 = $this->getJson('/api/fleet/size-mapping');
    expect($response1->json('version'))->toBe(1);

    // Update once
    $this->putJson('/api/fleet/size-mapping', [
        'sizes' => $this->customSizes,
    ]);

    $response2 = $this->getJson('/api/fleet/size-mapping');
    expect($response2->json('version'))->toBe(2);

    // Reset
    $this->postJson('/api/fleet/size-mapping/reset');

    $response3 = $this->getJson('/api/fleet/size-mapping');
    expect($response3->json('version'))->toBe(3);
});

test('only one configuration is active at a time', function () {
    // Update configuration multiple times
    for ($i = 1; $i <= 5; $i++) {
        $sizes = $this->customSizes;
        $sizes[0]['cm']['min'] = 45 + $i;
        $sizes[0]['cm']['max'] = 48 + $i;

        $this->putJson('/api/fleet/size-mapping', [
            'sizes' => $sizes,
        ]);
    }

    // Count active configurations
    $activeCount = \Illuminate\Support\Facades\DB::table('size_mapping_configurations')
        ->where('is_active', true)
        ->count();
    expect($activeCount)->toBe(1);

    // Verify latest version is active
    $this->assertDatabaseHas('size_mapping_configurations', [
        'version' => 6,
        'is_active' => true,
    ]);
});
