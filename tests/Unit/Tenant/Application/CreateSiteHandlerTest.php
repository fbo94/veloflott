<?php

declare(strict_types=1);

use Mockery\MockInterface;
use Tenant\Application\CreateSite\CreateSiteCommand;
use Tenant\Application\CreateSite\CreateSiteHandler;
use Tenant\Domain\Site;
use Tenant\Domain\SiteRepositoryInterface;
use Tenant\Domain\SiteStatus;
use Tenant\Domain\Tenant;
use Tenant\Domain\TenantRepositoryInterface;
use Tenant\Domain\TenantStatus;

beforeEach(function () {
    /** @var MockInterface&SiteRepositoryInterface $siteRepository */
    $siteRepository = Mockery::mock(SiteRepositoryInterface::class);
    $this->siteRepository = $siteRepository;

    /** @var MockInterface&TenantRepositoryInterface $tenantRepository */
    $tenantRepository = Mockery::mock(TenantRepositoryInterface::class);
    $this->tenantRepository = $tenantRepository;

    $this->handler = new CreateSiteHandler(
        $this->siteRepository,
        $this->tenantRepository,
    );
    $this->tenant = new Tenant(
        id: 'tenant-123',
        name: 'Test Tenant',
        slug: 'test-tenant',
        domain: null,
        status: TenantStatus::ACTIVE,
        contactEmail: null,
        contactPhone: null,
        settings: null,
    );
});

afterEach(function () {
    Mockery::close();
});

test('can create a site with minimal data', function () {
    // Arrange
    $command = new CreateSiteCommand(
        tenantId: 'tenant-123',
        name: 'Paris 11ème',
        slug: 'paris-11',
        address: null,
        city: null,
        postalCode: null,
        country: 'FR',
        phone: null,
        email: null,
        latitude: null,
        longitude: null,
        openingHours: null,
        settings: null,
    );

    $this->tenantRepository->shouldReceive('findById')
        ->with('tenant-123')
        ->once()
        ->andReturn($this->tenant);

    $this->siteRepository->shouldReceive('findBySlug')
        ->with('tenant-123', 'paris-11')
        ->once()
        ->andReturn(null);

    $this->siteRepository->shouldReceive('save')
        ->once()
        ->withArgs(function (Site $site) {
            return $site->name() === 'Paris 11ème'
                && $site->slug() === 'paris-11'
                && $site->tenantId() === 'tenant-123'
                && $site->status() === SiteStatus::ACTIVE;
        });

    // Act
    $site = $this->handler->handle($command);

    // Assert
    expect($site->name())->toBe('Paris 11ème');
    expect($site->slug())->toBe('paris-11');
    expect($site->tenantId())->toBe('tenant-123');
    expect($site->isActive())->toBeTrue();
});

test('can create a site with full data', function () {
    // Arrange
    $command = new CreateSiteCommand(
        tenantId: 'tenant-123',
        name: 'Paris 15ème',
        slug: 'paris-15',
        address: '100 Rue de Vaugirard',
        city: 'Paris',
        postalCode: '75015',
        country: 'FR',
        phone: '+33145678901',
        email: 'paris15@bikes.com',
        latitude: 48.8400,
        longitude: 2.3200,
        openingHours: ['monday' => ['open' => '09:00', 'close' => '19:00']],
        settings: ['max_rentals' => 50],
    );

    $this->tenantRepository->shouldReceive('findById')
        ->with('tenant-123')
        ->once()
        ->andReturn($this->tenant);

    $this->siteRepository->shouldReceive('findBySlug')
        ->with('tenant-123', 'paris-15')
        ->once()
        ->andReturn(null);

    $this->siteRepository->shouldReceive('save')->once();

    // Act
    $site = $this->handler->handle($command);

    // Assert
    expect($site->name())->toBe('Paris 15ème');
    expect($site->address())->toBe('100 Rue de Vaugirard');
    expect($site->city())->toBe('Paris');
    expect($site->phone())->toBe('+33145678901');
    expect($site->email())->toBe('paris15@bikes.com');
    expect($site->hasGeolocation())->toBeTrue();
    expect($site->latitude())->toBe(48.8400);
    expect($site->openingHours())->toBe(['monday' => ['open' => '09:00', 'close' => '19:00']]);
    expect($site->settings())->toBe(['max_rentals' => 50]);
});

test('throws exception when tenant not found', function () {
    // Arrange
    $command = new CreateSiteCommand(
        tenantId: 'nonexistent-tenant',
        name: 'Test Site',
        slug: 'test-site',
        address: null,
        city: null,
        postalCode: null,
        country: 'FR',
        phone: null,
        email: null,
        latitude: null,
        longitude: null,
        openingHours: null,
        settings: null,
    );

    $this->tenantRepository->shouldReceive('findById')
        ->with('nonexistent-tenant')
        ->once()
        ->andReturn(null);

    // Act & Assert
    expect(fn () => $this->handler->handle($command))
        ->toThrow(DomainException::class, 'Tenant not found');
});

test('throws exception when slug already exists', function () {
    // Arrange
    $command = new CreateSiteCommand(
        tenantId: 'tenant-123',
        name: 'Duplicate Site',
        slug: 'existing-slug',
        address: null,
        city: null,
        postalCode: null,
        country: 'FR',
        phone: null,
        email: null,
        latitude: null,
        longitude: null,
        openingHours: null,
        settings: null,
    );

    $existingSite = Site::create(
        id: 'existing-site',
        tenantId: 'tenant-123',
        name: 'Existing Site',
        slug: 'existing-slug',
    );

    $this->tenantRepository->shouldReceive('findById')
        ->with('tenant-123')
        ->once()
        ->andReturn($this->tenant);

    $this->siteRepository->shouldReceive('findBySlug')
        ->with('tenant-123', 'existing-slug')
        ->once()
        ->andReturn($existingSite);

    // Act & Assert
    expect(fn () => $this->handler->handle($command))
        ->toThrow(DomainException::class, 'A site with this slug already exists for this tenant');
});
