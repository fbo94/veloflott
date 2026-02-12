<?php

declare(strict_types=1);

namespace Tenant\Application\ListTenants;

use Subscription\Domain\SubscriptionPlanRepositoryInterface;
use Tenant\Domain\TenantRepositoryInterface;

final readonly class ListTenantsHandler
{
    public function __construct(
        private TenantRepositoryInterface $tenantRepository,
        private SubscriptionPlanRepositoryInterface $subscriptionPlanRepository,
    ) {
    }

    public function handle(ListTenantsQuery $query): ListTenantsResponse
    {
        $tenantsWithStats = $this->tenantRepository->findAllWithStats(
            status: $query->status,
            search: $query->search,
        );

        return new ListTenantsResponse($tenantsWithStats, $this->subscriptionPlanRepository);
    }
}
