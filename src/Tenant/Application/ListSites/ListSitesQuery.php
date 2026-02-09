<?php

declare(strict_types=1);

namespace Tenant\Application\ListSites;

final readonly class ListSitesQuery
{
    public function __construct(
        public string $tenantId,
        public bool $activeOnly = false,
    ) {}
}
