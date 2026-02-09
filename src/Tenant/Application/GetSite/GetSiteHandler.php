<?php

declare(strict_types=1);

namespace Tenant\Application\GetSite;

use Tenant\Domain\Site;
use Tenant\Domain\SiteRepositoryInterface;

final readonly class GetSiteHandler
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository,
    ) {}

    public function handle(GetSiteQuery $query): ?Site
    {
        return $this->siteRepository->findById($query->siteId);
    }
}
