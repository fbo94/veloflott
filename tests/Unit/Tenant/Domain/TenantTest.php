<?php

declare(strict_types=1);

use Tenant\Domain\Tenant;
use Tenant\Domain\TenantStatus;

test('can create a tenant with all fields', function () {
    // Arrange & Act
    $tenant = new Tenant(
        id: 'tenant-123',
        name: 'Véloflott Paris',
        slug: 'veloflott-paris',
        domain: 'veloflott.fr',
        status: TenantStatus::ACTIVE,
        contactEmail: 'contact@veloflott.fr',
        contactPhone: '+33123456789',
        settings: ['currency' => 'EUR', 'timezone' => 'Europe/Paris'],
        createdAt: new DateTimeImmutable('2024-01-15 10:00:00'),
        updatedAt: new DateTimeImmutable('2024-01-15 10:00:00'),
    );

    // Assert
    expect($tenant->id())->toBe('tenant-123');
    expect($tenant->name())->toBe('Véloflott Paris');
    expect($tenant->slug())->toBe('veloflott-paris');
    expect($tenant->domain())->toBe('veloflott.fr');
    expect($tenant->status())->toBe(TenantStatus::ACTIVE);
    expect($tenant->contactEmail())->toBe('contact@veloflott.fr');
    expect($tenant->contactPhone())->toBe('+33123456789');
    expect($tenant->settings())->toBe(['currency' => 'EUR', 'timezone' => 'Europe/Paris']);
    expect($tenant->isActive())->toBeTrue();
    expect($tenant->isSuspended())->toBeFalse();
});

test('can create a tenant with minimal fields', function () {
    // Arrange & Act
    $tenant = new Tenant(
        id: 'tenant-456',
        name: 'Minimal Bikes',
        slug: 'minimal-bikes',
        domain: null,
        status: TenantStatus::ACTIVE,
        contactEmail: null,
        contactPhone: null,
        settings: null,
    );

    // Assert
    expect($tenant->id())->toBe('tenant-456');
    expect($tenant->name())->toBe('Minimal Bikes');
    expect($tenant->domain())->toBeNull();
    expect($tenant->contactEmail())->toBeNull();
    expect($tenant->settings())->toBeNull();
});

test('can update tenant information', function () {
    // Arrange
    $tenant = new Tenant(
        id: 'tenant-789',
        name: 'Old Name',
        slug: 'old-name',
        domain: null,
        status: TenantStatus::ACTIVE,
        contactEmail: 'old@example.com',
        contactPhone: null,
        settings: null,
    );

    // Act
    $tenant->updateInformation(
        name: 'New Name',
        contactEmail: 'new@example.com',
        contactPhone: '+33987654321',
    );

    // Assert
    expect($tenant->name())->toBe('New Name');
    expect($tenant->contactEmail())->toBe('new@example.com');
    expect($tenant->contactPhone())->toBe('+33987654321');
});

test('can activate tenant', function () {
    // Arrange
    $tenant = new Tenant(
        id: 'tenant-activate',
        name: 'Suspended Tenant',
        slug: 'suspended-tenant',
        domain: null,
        status: TenantStatus::SUSPENDED,
        contactEmail: null,
        contactPhone: null,
        settings: null,
    );

    // Act
    $tenant->activate();

    // Assert
    expect($tenant->isActive())->toBeTrue();
    expect($tenant->status())->toBe(TenantStatus::ACTIVE);
});

test('can suspend tenant', function () {
    // Arrange
    $tenant = new Tenant(
        id: 'tenant-suspend',
        name: 'Active Tenant',
        slug: 'active-tenant',
        domain: null,
        status: TenantStatus::ACTIVE,
        contactEmail: null,
        contactPhone: null,
        settings: null,
    );

    // Act
    $tenant->suspend();

    // Assert
    expect($tenant->isSuspended())->toBeTrue();
    expect($tenant->status())->toBe(TenantStatus::SUSPENDED);
});

test('can archive tenant', function () {
    // Arrange
    $tenant = new Tenant(
        id: 'tenant-archive',
        name: 'Old Tenant',
        slug: 'old-tenant',
        domain: null,
        status: TenantStatus::ACTIVE,
        contactEmail: null,
        contactPhone: null,
        settings: null,
    );

    // Act
    $tenant->archive();

    // Assert
    expect($tenant->status())->toBe(TenantStatus::ARCHIVED);
});

test('can update settings', function () {
    // Arrange
    $tenant = new Tenant(
        id: 'tenant-settings',
        name: 'Settings Tenant',
        slug: 'settings-tenant',
        domain: null,
        status: TenantStatus::ACTIVE,
        contactEmail: null,
        contactPhone: null,
        settings: null,
    );

    // Act
    $tenant->updateSettings(['currency' => 'USD', 'max_bikes' => 100]);

    // Assert
    expect($tenant->settings())->toBe(['currency' => 'USD', 'max_bikes' => 100]);
});
