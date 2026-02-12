<?php

declare(strict_types=1);

use Subscription\Domain\SubscriptionPlan;
use Subscription\Domain\SubscriptionPlanRepositoryInterface;
use Tenant\Application\ListTenants\ListTenantsHandler;
use Tenant\Application\ListTenants\ListTenantsQuery;
use Tenant\Domain\Tenant;
use Tenant\Domain\TenantRepositoryInterface;
use Tenant\Domain\TenantStatus;

beforeEach(function () {
    $this->tenantRepository = Mockery::mock(TenantRepositoryInterface::class);
    $this->subscriptionPlanRepository = Mockery::mock(SubscriptionPlanRepositoryInterface::class);
    $this->handler = new ListTenantsHandler(
        $this->tenantRepository,
        $this->subscriptionPlanRepository
    );
});

afterEach(function () {
    Mockery::close();
});

test('should list all tenants with stats', function () {
    // Arrange
    $tenant = new Tenant(
        id: '123e4567-e89b-12d3-a456-426614174000',
        name: 'Test Tenant',
        slug: 'test-tenant',
        domain: 'test.example.com',
        status: TenantStatus::ACTIVE,
        contactEmail: 'contact@test.com',
        contactPhone: '+33123456789',
        settings: null,
        subscriptionPlanId: 'plan-123'
    );

    $plan = new SubscriptionPlan(
        id: 'plan-123',
        name: 'pro',
        displayName: 'Pro',
        description: 'Pro plan',
        priceMonthly: 99.00,
        priceYearly: 990.00,
        maxUsers: 25,
        maxBikes: 250,
        maxSites: 5,
        features: ['api_access' => true],
        isActive: true,
        sortOrder: 1
    );

    $tenantsWithStats = [
        [
            'tenant' => $tenant,
            'bikes_count' => 12,
            'sites_count' => 3,
            'users_count' => 0,
        ],
    ];

    $this->tenantRepository
        ->shouldReceive('findAllWithStats')
        ->once()
        ->with(null, null)
        ->andReturn($tenantsWithStats);

    $this->subscriptionPlanRepository
        ->shouldReceive('findById')
        ->once()
        ->with('plan-123')
        ->andReturn($plan);

    $query = new ListTenantsQuery(null, null);

    // Act
    $response = $this->handler->handle($query);
    $result = $response->toArray();

    // Assert
    expect($result)->toHaveKey('tenants');
    expect($result)->toHaveKey('total');
    expect($result['total'])->toBe(1);
    expect($result['tenants'][0])->toHaveKey('id', '123e4567-e89b-12d3-a456-426614174000');
    expect($result['tenants'][0])->toHaveKey('name', 'Test Tenant');
    expect($result['tenants'][0])->toHaveKey('usage');
    expect($result['tenants'][0]['usage'])->toBe([
        'users_count' => 0,
        'bikes_count' => 12,
        'sites_count' => 3,
    ]);
    expect($result['tenants'][0])->toHaveKey('subscription_plan');
    expect($result['tenants'][0]['subscription_plan']['name'])->toBe('pro');
});

test('should filter tenants by status', function () {
    // Arrange
    $tenant = new Tenant(
        id: '123e4567-e89b-12d3-a456-426614174000',
        name: 'Active Tenant',
        slug: 'active-tenant',
        domain: 'active.example.com',
        status: TenantStatus::ACTIVE,
        contactEmail: 'contact@active.com',
        contactPhone: '+33123456789',
        settings: null
    );

    $tenantsWithStats = [
        [
            'tenant' => $tenant,
            'bikes_count' => 5,
            'sites_count' => 1,
            'users_count' => 0,
        ],
    ];

    $this->tenantRepository
        ->shouldReceive('findAllWithStats')
        ->once()
        ->with('active', null)
        ->andReturn($tenantsWithStats);

    $this->subscriptionPlanRepository
        ->shouldReceive('findById')
        ->andReturn(null);

    $query = new ListTenantsQuery('active', null);

    // Act
    $response = $this->handler->handle($query);
    $result = $response->toArray();

    // Assert
    expect($result['total'])->toBe(1);
    expect($result['tenants'][0]['status'])->toBe(TenantStatus::ACTIVE);
});

test('should search tenants by text', function () {
    // Arrange
    $tenant = new Tenant(
        id: '123e4567-e89b-12d3-a456-426614174000',
        name: 'Paris Bikes',
        slug: 'paris-bikes',
        domain: 'paris.example.com',
        status: TenantStatus::ACTIVE,
        contactEmail: 'contact@paris.com',
        contactPhone: '+33123456789',
        settings: null
    );

    $tenantsWithStats = [
        [
            'tenant' => $tenant,
            'bikes_count' => 20,
            'sites_count' => 4,
            'users_count' => 0,
        ],
    ];

    $this->tenantRepository
        ->shouldReceive('findAllWithStats')
        ->once()
        ->with(null, 'paris')
        ->andReturn($tenantsWithStats);

    $this->subscriptionPlanRepository
        ->shouldReceive('findById')
        ->andReturn(null);

    $query = new ListTenantsQuery(null, 'paris');

    // Act
    $response = $this->handler->handle($query);
    $result = $response->toArray();

    // Assert
    expect($result['total'])->toBe(1);
    expect($result['tenants'][0]['name'])->toBe('Paris Bikes');
});

test('should handle empty results', function () {
    // Arrange
    $this->tenantRepository
        ->shouldReceive('findAllWithStats')
        ->once()
        ->with(null, null)
        ->andReturn([]);

    $query = new ListTenantsQuery(null, null);

    // Act
    $response = $this->handler->handle($query);
    $result = $response->toArray();

    // Assert
    expect($result['total'])->toBe(0);
    expect($result['tenants'])->toBe([]);
});
