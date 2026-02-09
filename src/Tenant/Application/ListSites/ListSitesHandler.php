<?php

declare(strict_types=1);

namespace Tenant\Application\ListSites;

use Tenant\Domain\Site;
use Tenant\Domain\SiteRepositoryInterface;

final readonly class ListSitesHandler
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository,
    ) {}

    /**
     * @return array<Site>
     */
    public function handle(ListSitesQuery $query): array
    {
        if ($query->activeOnly) {
            return $this->siteRepository->findActiveByTenantId($query->tenantId);
        }

        return $this->siteRepository->findByTenantId($query->tenantId);
    }
}
