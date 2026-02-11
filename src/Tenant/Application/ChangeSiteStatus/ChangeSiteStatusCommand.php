<?php

declare(strict_types=1);

namespace Tenant\Application\ChangeSiteStatus;

use Tenant\Domain\SiteStatus;

final readonly class ChangeSiteStatusCommand
{
    public function __construct(
        public string $siteId,
        public SiteStatus $status,
    ) {
    }
}
