<?php

declare(strict_types=1);

use Fleet\Domain\BikeStatus;
use Fleet\Domain\FrameSizeLetter;
use Fleet\Domain\FrameSizeUnit;
use Fleet\Domain\PricingTier;
use Fleet\Infrastructure\Persistence\Models\BikeEloquentModel;
use Fleet\Infrastructure\Persistence\Models\BrandEloquentModel;
use Fleet\Infrastructure\Persistence\Models\CategoryEloquentModel;
use Fleet\Infrastructure\Persistence\Models\ModelEloquentModel;
use Ramsey\Uuid\Uuid;
use Tenant\Application\TenantContext;
use Tenant\Domain\Tenant;
use Tenant\Domain\TenantStatus;
use Tenant\Infrastructure\Persistence\Models\TenantEloquentModel;

beforeEach(function () {
    $this->withoutMiddleware();

    // Create two tenants
    $this->tenant1Id = Uuid::uuid4()->toString();
    $this->tenant2Id = Uuid::uuid4()->toString();

    TenantEloquentModel::create([
        'id' => $this->tenant1Id,
        'name' => 'Tenant 1',
        'slug' => 'tenant-1',
        'status' => 'active',
    ]);

    TenantEloquentModel::create([
        'id' => $this->tenant2Id,
        'name' => 'Tenant 2',
        'slug' => 'tenant-2',
        'status' => 'active',
    ]);

    // Create brands for each tenant
    $this->brandT1 = BrandEloquentModel::create([
        'id' => Uuid::uuid4()->toString(),
        'tenant_id' => $this->tenant1Id,
        'name' => 'Brand T1',
    ]);

    $this->brandT2 = BrandEloquentModel::create([
        'id' => Uuid::uuid4()->toString(),
        'tenant_id' => $this->tenant2Id,
        'name' => 'Brand T2',
    ]);

    // Create categories for each tenant
    $this->categoryT1 = CategoryEloquentModel::create([
        'id' => Uuid::uuid4()->toString(),
        'tenant_id' => $this->tenant1Id,
        'name' => 'VTT Tenant 1',
        'slug' => 'vtt-t1',
        'description' => 'Vélo Tout Terrain - Tenant 1',
    ]);

    $this->categoryT2 = CategoryEloquentModel::create([
        'id' => Uuid::uuid4()->toString(),
        'tenant_id' => $this->tenant2Id,
        'name' => 'VTT Tenant 2',
        'slug' => 'vtt-t2',
        'description' => 'Vélo Tout Terrain - Tenant 2',
    ]);

    // Create models for each tenant
    $this->modelT1 = ModelEloquentModel::create([
        'id' => Uuid::uuid4()->toString(),
        'tenant_id' => $this->tenant1Id,
        'name' => 'Model T1',
        'brand_id' => $this->brandT1->id,
    ]);

    $this->modelT2 = ModelEloquentModel::create([
        'id' => Uuid::uuid4()->toString(),
        'tenant_id' => $this->tenant2Id,
        'name' => 'Model T2',
        'brand_id' => $this->brandT2->id,
    ]);

    $this->tenantContext = app(TenantContext::class);
});

test('bikes are isolated by tenant - tenant 1 sees only their bikes', function () {
    // Create bikes for both tenants
    BikeEloquentModel::create([
        'id' => Uuid::uuid4()->toString(),
        'tenant_id' => $this->tenant1Id,
        'qr_code_uuid' => Uuid::uuid4()->toString(),
        'internal_number' => 'BIKE-T1-001',
        'model_id' => $this->modelT1->id,
        'category_id' => $this->categoryT1->id,
        'frame_size_unit' => FrameSizeUnit::LETTER->value,
        'frame_size_letter' => FrameSizeLetter::M->value,
        'frame_size_letter_equivalent' => FrameSizeLetter::M->value,
        'status' => BikeStatus::AVAILABLE->value,
        'pricing_tier' => PricingTier::STANDARD->value,
    ]);

    BikeEloquentModel::create([
        'id' => Uuid::uuid4()->toString(),
        'tenant_id' => $this->tenant1Id,
        'qr_code_uuid' => Uuid::uuid4()->toString(),
        'internal_number' => 'BIKE-T1-002',
        'model_id' => $this->modelT1->id,
        'category_id' => $this->categoryT1->id,
        'frame_size_unit' => FrameSizeUnit::LETTER->value,
        'frame_size_letter' => FrameSizeLetter::L->value,
        'frame_size_letter_equivalent' => FrameSizeLetter::L->value,
        'status' => BikeStatus::AVAILABLE->value,
        'pricing_tier' => PricingTier::STANDARD->value,
    ]);

    BikeEloquentModel::create([
        'id' => Uuid::uuid4()->toString(),
        'tenant_id' => $this->tenant2Id,
        'qr_code_uuid' => Uuid::uuid4()->toString(),
        'internal_number' => 'BIKE-T2-001',
        'model_id' => $this->modelT2->id,
        'category_id' => $this->categoryT2->id,
        'frame_size_unit' => FrameSizeUnit::LETTER->value,
        'frame_size_letter' => FrameSizeLetter::M->value,
        'frame_size_letter_equivalent' => FrameSizeLetter::M->value,
        'status' => BikeStatus::AVAILABLE->value,
        'pricing_tier' => PricingTier::STANDARD->value,
    ]);

    // Set context to tenant 1
    $this->tenantContext->setTenant(new Tenant(
        id: $this->tenant1Id,
        name: 'Tenant 1',
        slug: 'tenant-1',
        domain: null,
        status: TenantStatus::ACTIVE,
        contactEmail: null,
        contactPhone: null,
        settings: null,
    ));

    // This should only return tenant 1's bikes
    $tenant1Bikes = BikeEloquentModel::all();
    expect($tenant1Bikes)->toHaveCount(2);
    foreach ($tenant1Bikes as $bike) {
        expect($bike->tenant_id)->toBe($this->tenant1Id);
    }
});

test('bikes are isolated by tenant - tenant 2 sees only their bikes', function () {
    // Create bikes for both tenants
    BikeEloquentModel::create([
        'id' => Uuid::uuid4()->toString(),
        'tenant_id' => $this->tenant1Id,
        'qr_code_uuid' => Uuid::uuid4()->toString(),
        'internal_number' => 'BIKE-T1-001',
        'model_id' => $this->modelT1->id,
        'category_id' => $this->categoryT1->id,
        'frame_size_unit' => FrameSizeUnit::LETTER->value,
        'frame_size_letter' => FrameSizeLetter::M->value,
        'frame_size_letter_equivalent' => FrameSizeLetter::M->value,
        'status' => BikeStatus::AVAILABLE->value,
        'pricing_tier' => PricingTier::STANDARD->value,
    ]);

    BikeEloquentModel::create([
        'id' => Uuid::uuid4()->toString(),
        'tenant_id' => $this->tenant2Id,
        'qr_code_uuid' => Uuid::uuid4()->toString(),
        'internal_number' => 'BIKE-T2-001',
        'model_id' => $this->modelT2->id,
        'category_id' => $this->categoryT2->id,
        'frame_size_unit' => FrameSizeUnit::LETTER->value,
        'frame_size_letter' => FrameSizeLetter::M->value,
        'frame_size_letter_equivalent' => FrameSizeLetter::M->value,
        'status' => BikeStatus::AVAILABLE->value,
        'pricing_tier' => PricingTier::STANDARD->value,
    ]);

    // Set context to tenant 2
    $this->tenantContext->setTenant(new Tenant(
        id: $this->tenant2Id,
        name: 'Tenant 2',
        slug: 'tenant-2',
        domain: null,
        status: TenantStatus::ACTIVE,
        contactEmail: null,
        contactPhone: null,
        settings: null,
    ));

    // This should only return tenant 2's bikes
    $tenant2Bikes = BikeEloquentModel::all();
    expect($tenant2Bikes)->toHaveCount(1);
    expect($tenant2Bikes->first()->tenant_id)->toBe($this->tenant2Id);
});

test('tenant 1 cannot access tenant 2 bike by id', function () {
    $bikeT2 = BikeEloquentModel::create([
        'id' => Uuid::uuid4()->toString(),
        'tenant_id' => $this->tenant2Id,
        'qr_code_uuid' => Uuid::uuid4()->toString(),
        'internal_number' => 'BIKE-T2-001',
        'model_id' => $this->modelT2->id,
        'category_id' => $this->categoryT2->id,
        'frame_size_unit' => FrameSizeUnit::LETTER->value,
        'frame_size_letter' => FrameSizeLetter::M->value,
        'frame_size_letter_equivalent' => FrameSizeLetter::M->value,
        'status' => BikeStatus::AVAILABLE->value,
        'pricing_tier' => PricingTier::STANDARD->value,
    ]);

    // Set context to tenant 1
    $this->tenantContext->setTenant(new Tenant(
        id: $this->tenant1Id,
        name: 'Tenant 1',
        slug: 'tenant-1',
        domain: null,
        status: TenantStatus::ACTIVE,
        contactEmail: null,
        contactPhone: null,
        settings: null,
    ));

    // Try to find tenant 2's bike - should return null
    $foundBike = BikeEloquentModel::find($bikeT2->id);
    expect($foundBike)->toBeNull();
});

test('TenantScoped trait filters bikes automatically', function () {
    // Create bikes for both tenants without scope
    BikeEloquentModel::withoutGlobalScopes()->create([
        'id' => Uuid::uuid4()->toString(),
        'tenant_id' => $this->tenant1Id,
        'qr_code_uuid' => Uuid::uuid4()->toString(),
        'internal_number' => 'BIKE-T1-001',
        'model_id' => $this->modelT1->id,
        'category_id' => $this->categoryT1->id,
        'frame_size_unit' => FrameSizeUnit::LETTER->value,
        'frame_size_letter' => FrameSizeLetter::M->value,
        'frame_size_letter_equivalent' => FrameSizeLetter::M->value,
        'status' => BikeStatus::AVAILABLE->value,
        'pricing_tier' => PricingTier::STANDARD->value,
    ]);

    BikeEloquentModel::withoutGlobalScopes()->create([
        'id' => Uuid::uuid4()->toString(),
        'tenant_id' => $this->tenant1Id,
        'qr_code_uuid' => Uuid::uuid4()->toString(),
        'internal_number' => 'BIKE-T1-002',
        'model_id' => $this->modelT1->id,
        'category_id' => $this->categoryT1->id,
        'frame_size_unit' => FrameSizeUnit::LETTER->value,
        'frame_size_letter' => FrameSizeLetter::L->value,
        'frame_size_letter_equivalent' => FrameSizeLetter::L->value,
        'status' => BikeStatus::AVAILABLE->value,
        'pricing_tier' => PricingTier::STANDARD->value,
    ]);

    BikeEloquentModel::withoutGlobalScopes()->create([
        'id' => Uuid::uuid4()->toString(),
        'tenant_id' => $this->tenant2Id,
        'qr_code_uuid' => Uuid::uuid4()->toString(),
        'internal_number' => 'BIKE-T2-001',
        'model_id' => $this->modelT2->id,
        'category_id' => $this->categoryT2->id,
        'frame_size_unit' => FrameSizeUnit::LETTER->value,
        'frame_size_letter' => FrameSizeLetter::M->value,
        'frame_size_letter_equivalent' => FrameSizeLetter::M->value,
        'status' => BikeStatus::AVAILABLE->value,
        'pricing_tier' => PricingTier::STANDARD->value,
    ]);

    // Set context to tenant 1
    $this->tenantContext->setTenant(new Tenant(
        id: $this->tenant1Id,
        name: 'Tenant 1',
        slug: 'tenant-1',
        domain: null,
        status: TenantStatus::ACTIVE,
        contactEmail: null,
        contactPhone: null,
        settings: null,
    ));

    // This should only return tenant 1's bikes
    $tenant1Bikes = BikeEloquentModel::all();
    expect($tenant1Bikes)->toHaveCount(2);
    foreach ($tenant1Bikes as $bike) {
        expect($bike->tenant_id)->toBe($this->tenant1Id);
    }
});
