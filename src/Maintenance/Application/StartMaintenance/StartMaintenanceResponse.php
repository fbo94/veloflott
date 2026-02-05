<?php

declare(strict_types=1);

namespace Maintenance\Application\StartMaintenance;

final readonly class StartMaintenanceResponse
{
    public function __construct(
        public string $maintenanceId,
        public string $message,
    ) {
    }
}
