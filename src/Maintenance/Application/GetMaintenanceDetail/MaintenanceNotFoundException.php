<?php

declare(strict_types=1);

namespace Maintenance\Application\GetMaintenanceDetail;

final class MaintenanceNotFoundException extends \Exception
{
    public function __construct(string $maintenanceId)
    {
        parent::__construct("Maintenance with ID {$maintenanceId} not found");
    }
}
