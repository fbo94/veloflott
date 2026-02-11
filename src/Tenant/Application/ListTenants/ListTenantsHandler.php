<?php

declare(strict_types=1);

namespace Tenant\Application\ListTenants;

use Tenant\Domain\TenantRepositoryInterface;

final readonly class ListTenantsHandler
{
    public function __construct(
        private TenantRepositoryInterface $tenantRepository,
    ) {
    }

    public function handle(ListTenantsQuery $query): ListTenantsResponse
    {
        $tenants = $this->tenantRepository->findAll(
            status: $query->status,
            search: $query->search,
        );

        return new ListTenantsResponse($tenants);
    }
}
