<?php

declare(strict_types=1);

namespace Tenant\Application\DeleteSite;

final readonly class DeleteSiteCommand
{
    public function __construct(
        public string $siteId,
    ) {
    }
}
