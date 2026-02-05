<?php

declare(strict_types=1);

namespace Maintenance\Application\GetMaintenanceDetail;

final readonly class GetMaintenanceDetailQuery
{
    public function __construct(
        public string $maintenanceId,
    ) {
    }
}
