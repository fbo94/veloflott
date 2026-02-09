<?php

declare(strict_types=1);

use Tenant\Domain\Site;
use Tenant\Domain\SiteStatus;

test('can create a site using factory method', function () {
    // Arrange & Act
    $site = Site::create(
        id: 'site-123',
        tenantId: 'tenant-456',
        name: 'Paris 11ème',
        slug: 'paris-11',
        address: '15 Rue de la Roquette',
        city: 'Paris',
        postalCode: '75011',
        country: 'FR',
    );

    // Assert
    expect($site->id())->toBe('site-123');
    expect($site->tenantId())->toBe('tenant-456');
    expect($site->name())->toBe('Paris 11ème');
    expect($site->slug())->toBe('paris-11');
    expect($site->address())->toBe('15 Rue de la Roquette');
    expect($site->city())->toBe('Paris');
    expect($site->postalCode())->toBe('75011');
    expect($site->country())->toBe('FR');
    expect($site->status())->toBe(SiteStatus::ACTIVE);
    expect($site->isActive())->toBeTrue();
});

test('can create a site with minimal fields', function () {
    // Arrange & Act
    $site = Site::create(
        id: 'site-minimal',
        tenantId: 'tenant-456',
        name: 'Minimal Site',
        slug: 'minimal-site',
    );

    // Assert
    expect($site->id())->toBe('site-minimal');
    expect($site->name())->toBe('Minimal Site');
    expect($site->address())->toBeNull();
    expect($site->city())->toBeNull();
    expect($site->country())->toBe('FR'); // default
    expect($site->status())->toBe(SiteStatus::ACTIVE);
});

test('can reconstitute a site from persistence', function () {
    // Arrange & Act
    $now = new DateTimeImmutable();
    $site = Site::reconstitute(
        id: 'site-recon',
        tenantId: 'tenant-789',
        name: 'Reconstituted Site',
        slug: 'recon-site',
        address: '123 Main St',
        city: 'Lyon',
        postalCode: '69001',
        country: 'FR',
        phone: '+33478123456',
        email: 'lyon@bikes.com',
        status: SiteStatus::SUSPENDED,
        openingHours: ['monday' => ['open' => '09:00', 'close' => '19:00']],
        settings: ['max_rentals' => 50],
        latitude: 45.764043,
        longitude: 4.835659,
        createdAt: $now,
        updatedAt: $now,
    );

    // Assert
    expect($site->id())->toBe('site-recon');
    expect($site->phone())->toBe('+33478123456');
    expect($site->email())->toBe('lyon@bikes.com');
    expect($site->status())->toBe(SiteStatus::SUSPENDED);
    expect($site->openingHours())->toBe(['monday' => ['open' => '09:00', 'close' => '19:00']]);
    expect($site->settings())->toBe(['max_rentals' => 50]);
    expect($site->latitude())->toBe(45.764043);
    expect($site->longitude())->toBe(4.835659);
    expect($site->isSuspended())->toBeTrue();
});

test('can update site information', function () {
    // Arrange
    $site = Site::create(
        id: 'site-update',
        tenantId: 'tenant-123',
        name: 'Old Name',
        slug: 'old-name',
    );

    // Act
    $site->updateInformation(
        name: 'New Name',
        address: '456 New Street',
        city: 'Nice',
        postalCode: '06000',
        country: 'FR',
        phone: '+33493123456',
        email: 'nice@bikes.com',
    );

    // Assert
    expect($site->name())->toBe('New Name');
    expect($site->address())->toBe('456 New Street');
    expect($site->city())->toBe('Nice');
    expect($site->phone())->toBe('+33493123456');
    expect($site->email())->toBe('nice@bikes.com');
});

test('can set and clear geolocation', function () {
    // Arrange
    $site = Site::create(
        id: 'site-geo',
        tenantId: 'tenant-123',
        name: 'Geo Site',
        slug: 'geo-site',
    );

    // Act & Assert - Initially no geolocation
    expect($site->hasGeolocation())->toBeFalse();
    expect($site->coordinates())->toBeNull();

    // Set geolocation
    $site->setGeolocation(48.8566, 2.3522);
    expect($site->hasGeolocation())->toBeTrue();
    expect($site->latitude())->toBe(48.8566);
    expect($site->longitude())->toBe(2.3522);
    expect($site->coordinates())->toBe(['latitude' => 48.8566, 'longitude' => 2.3522]);

    // Clear geolocation
    $site->clearGeolocation();
    expect($site->hasGeolocation())->toBeFalse();
    expect($site->coordinates())->toBeNull();
});

test('can compute full address', function () {
    // Arrange
    $site = Site::create(
        id: 'site-addr',
        tenantId: 'tenant-123',
        name: 'Address Site',
        slug: 'addr-site',
        address: '10 Rue de la Paix',
        city: 'Paris',
        postalCode: '75002',
        country: 'FR',
    );

    // Assert
    expect($site->fullAddress())->toBe('10 Rue de la Paix, 75002 Paris, FR');
});

test('fullAddress returns null if no address', function () {
    // Arrange
    $site = Site::create(
        id: 'site-no-addr',
        tenantId: 'tenant-123',
        name: 'No Address Site',
        slug: 'no-addr-site',
    );

    // Assert
    expect($site->fullAddress())->toBeNull();
});

test('can activate site', function () {
    // Arrange
    $site = Site::reconstitute(
        id: 'site-activate',
        tenantId: 'tenant-123',
        name: 'Suspended Site',
        slug: 'suspended-site',
        address: null,
        city: null,
        postalCode: null,
        country: 'FR',
        phone: null,
        email: null,
        status: SiteStatus::SUSPENDED,
        openingHours: null,
        settings: null,
        latitude: null,
        longitude: null,
        createdAt: new DateTimeImmutable(),
        updatedAt: new DateTimeImmutable(),
    );

    // Act
    $site->activate();

    // Assert
    expect($site->isActive())->toBeTrue();
    expect($site->canAcceptRentals())->toBeTrue();
});

test('can suspend site', function () {
    // Arrange
    $site = Site::create(
        id: 'site-suspend',
        tenantId: 'tenant-123',
        name: 'Active Site',
        slug: 'active-site',
    );

    // Act
    $site->suspend();

    // Assert
    expect($site->isSuspended())->toBeTrue();
    expect($site->canAcceptRentals())->toBeFalse();
});

test('can close site', function () {
    // Arrange
    $site = Site::create(
        id: 'site-close',
        tenantId: 'tenant-123',
        name: 'Open Site',
        slug: 'open-site',
    );

    // Act
    $site->close();

    // Assert
    expect($site->isClosed())->toBeTrue();
    expect($site->canAcceptRentals())->toBeFalse();
});

test('can set opening hours', function () {
    // Arrange
    $site = Site::create(
        id: 'site-hours',
        tenantId: 'tenant-123',
        name: 'Hours Site',
        slug: 'hours-site',
    );

    $openingHours = [
        'monday' => ['open' => '09:00', 'close' => '19:00'],
        'tuesday' => ['open' => '09:00', 'close' => '19:00'],
        'saturday' => ['open' => '10:00', 'close' => '18:00'],
    ];

    // Act
    $site->setOpeningHours($openingHours);

    // Assert
    expect($site->openingHours())->toBe($openingHours);
});

test('can update settings', function () {
    // Arrange
    $site = Site::create(
        id: 'site-settings',
        tenantId: 'tenant-123',
        name: 'Settings Site',
        slug: 'settings-site',
    );

    // Act
    $site->updateSettings(['max_rentals' => 100, 'allow_reservations' => true]);

    // Assert
    expect($site->settings())->toBe(['max_rentals' => 100, 'allow_reservations' => true]);
});

test('belongsToTenant validates correctly', function () {
    // Arrange
    $site = Site::create(
        id: 'site-belongs',
        tenantId: 'tenant-123',
        name: 'Belongs Site',
        slug: 'belongs-site',
    );

    // Assert
    expect($site->belongsToTenant('tenant-123'))->toBeTrue();
    expect($site->belongsToTenant('tenant-456'))->toBeFalse();
});
