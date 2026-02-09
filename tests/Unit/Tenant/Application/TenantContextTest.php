<?php

declare(strict_types=1);

use Tenant\Application\TenantContext;
use Tenant\Domain\Site;
use Tenant\Domain\SiteStatus;
use Tenant\Domain\Tenant;
use Tenant\Domain\TenantStatus;

beforeEach(function () {
    $this->context = new TenantContext();
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
    $this->site = Site::create(
        id: 'site-456',
        tenantId: 'tenant-123',
        name: 'Test Site',
        slug: 'test-site',
    );
});

test('context is empty by default', function () {
    expect($this->context->hasTenant())->toBeFalse();
    expect($this->context->hasSite())->toBeFalse();
    expect($this->context->isResolved())->toBeFalse();
    expect($this->context->tenant())->toBeNull();
    expect($this->context->site())->toBeNull();
    expect($this->context->getTenantId())->toBeNull();
    expect($this->context->getSiteId())->toBeNull();
});

test('can set tenant', function () {
    // Act
    $this->context->setTenant($this->tenant);

    // Assert
    expect($this->context->hasTenant())->toBeTrue();
    expect($this->context->isResolved())->toBeTrue();
    expect($this->context->tenant())->toBe($this->tenant);
    expect($this->context->getTenantId())->toBe('tenant-123');
});

test('can set site after tenant', function () {
    // Arrange
    $this->context->setTenant($this->tenant);

    // Act
    $this->context->setSite($this->site);

    // Assert
    expect($this->context->hasSite())->toBeTrue();
    expect($this->context->site())->toBe($this->site);
    expect($this->context->getSiteId())->toBe('site-456');
});

test('cannot set site without tenant', function () {
    // Act & Assert
    expect(fn () => $this->context->setSite($this->site))
        ->toThrow(LogicException::class, 'Cannot set site without tenant context');
});

test('cannot set site belonging to different tenant', function () {
    // Arrange
    $otherTenant = new Tenant(
        id: 'other-tenant',
        name: 'Other Tenant',
        slug: 'other-tenant',
        domain: null,
        status: TenantStatus::ACTIVE,
        contactEmail: null,
        contactPhone: null,
        settings: null,
    );
    $this->context->setTenant($otherTenant);

    // Act & Assert
    expect(fn () => $this->context->setSite($this->site))
        ->toThrow(LogicException::class, 'Site does not belong to current tenant');
});

test('requireTenant throws when not set', function () {
    expect(fn () => $this->context->requireTenant())
        ->toThrow(RuntimeException::class, 'Tenant context is not resolved');
});

test('requireTenantId throws when not set', function () {
    expect(fn () => $this->context->requireTenantId())
        ->toThrow(RuntimeException::class, 'Tenant context is not resolved');
});

test('requireSite throws when not set', function () {
    $this->context->setTenant($this->tenant);

    expect(fn () => $this->context->requireSite())
        ->toThrow(RuntimeException::class, 'Site context is not resolved');
});

test('requireSiteId throws when not set', function () {
    $this->context->setTenant($this->tenant);

    expect(fn () => $this->context->requireSiteId())
        ->toThrow(RuntimeException::class, 'Site context is not resolved');
});

test('requireTenant returns tenant when set', function () {
    $this->context->setTenant($this->tenant);

    expect($this->context->requireTenant())->toBe($this->tenant);
    expect($this->context->requireTenantId())->toBe('tenant-123');
});

test('requireSite returns site when set', function () {
    $this->context->setTenant($this->tenant);
    $this->context->setSite($this->site);

    expect($this->context->requireSite())->toBe($this->site);
    expect($this->context->requireSiteId())->toBe('site-456');
});

test('can clear context', function () {
    // Arrange
    $this->context->setTenant($this->tenant);
    $this->context->setSite($this->site);

    // Act
    $this->context->clear();

    // Assert
    expect($this->context->hasTenant())->toBeFalse();
    expect($this->context->hasSite())->toBeFalse();
    expect($this->context->tenant())->toBeNull();
    expect($this->context->site())->toBeNull();
});

test('belongsToCurrentTenant validates correctly', function () {
    $this->context->setTenant($this->tenant);

    expect($this->context->belongsToCurrentTenant('tenant-123'))->toBeTrue();
    expect($this->context->belongsToCurrentTenant('tenant-999'))->toBeFalse();
});

test('belongsToCurrentTenant returns false without tenant', function () {
    expect($this->context->belongsToCurrentTenant('tenant-123'))->toBeFalse();
});

test('belongsToCurrentSite validates correctly', function () {
    $this->context->setTenant($this->tenant);
    $this->context->setSite($this->site);

    expect($this->context->belongsToCurrentSite('site-456'))->toBeTrue();
    expect($this->context->belongsToCurrentSite('site-999'))->toBeFalse();
});

test('belongsToCurrentSite returns false without site', function () {
    $this->context->setTenant($this->tenant);

    expect($this->context->belongsToCurrentSite('site-456'))->toBeFalse();
});
