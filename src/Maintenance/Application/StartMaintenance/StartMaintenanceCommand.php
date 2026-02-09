<?php

declare(strict_types=1);

namespace Maintenance\Application\StartMaintenance;

final readonly class StartMaintenanceCommand
{
    public function __construct(
        public string $maintenanceId,
    ) {
    }
}
