<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Illuminate\Support\Str;
use Tenant\Application\TenantContext;
use Tenant\Domain\Tenant;
use Tenant\Domain\TenantStatus;
use Tenant\Infrastructure\Persistence\Models\TenantEloquentModel;

trait SetupTenantContext
{
    protected TenantEloquentModel $testTenant;

    protected function setUpTenantContext(): void
    {
        $this->testTenant = TenantEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Test Tenant',
            'slug' => 'test-tenant',
            'status' => TenantStatus::ACTIVE->value,
        ]);

        $tenantContext = app(TenantContext::class);
        $tenantContext->setTenant(new Tenant(
            id: $this->testTenant->id,
            name: $this->testTenant->name,
            slug: $this->testTenant->slug,
            domain: null,
            status: TenantStatus::ACTIVE,
            contactEmail: null,
            contactPhone: null,
            settings: null,
        ));
    }
}
