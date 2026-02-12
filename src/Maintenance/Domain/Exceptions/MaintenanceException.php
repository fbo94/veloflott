<?php

declare(strict_types=1);

namespace Maintenance\Domain\Exceptions;

use Shared\Domain\DomainException;

class MaintenanceException extends DomainException
{
    public static function notFound(string $maintenanceId): self
    {
        return new self("Maintenance with ID {$maintenanceId} not found");
    }

    public static function bikeNotFound(string $bikeId): self
    {
        return new self("Bike with ID {$bikeId} not found");
    }

    public static function bikeNotAvailableForMaintenance(string $bikeId, string $currentStatus): self
    {
        return new self("Bike {$bikeId} cannot be put in maintenance (current status: {$currentStatus})");
    }

    public static function cannotStart(string $maintenanceId, string $reason): self
    {
        return new self("Cannot start maintenance {$maintenanceId}: {$reason}");
    }

    public static function cannotComplete(string $maintenanceId, string $reason): self
    {
        return new self("Cannot complete maintenance {$maintenanceId}: {$reason}");
    }

    public static function cannotModify(string $maintenanceId, string $reason): self
    {
        return new self("Cannot modify maintenance {$maintenanceId}: {$reason}");
    }
}
