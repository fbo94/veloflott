<?php

declare(strict_types=1);

namespace Tenant\Application\GetSite;

final readonly class GetSiteQuery
{
    public function __construct(
        public string $siteId,
    ) {}
}
