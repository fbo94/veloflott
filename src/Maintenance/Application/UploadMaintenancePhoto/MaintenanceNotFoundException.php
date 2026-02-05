<?php

declare(strict_types=1);

namespace Maintenance\Application\UploadMaintenancePhoto;

use Shared\Domain\DomainException;

final class MaintenanceNotFoundException extends DomainException
{
    protected string $errorCode = 'MAINTENANCE_NOT_FOUND';

    public function __construct(private readonly string $maintenanceId)
    {
        parent::__construct("La maintenance '{$maintenanceId}' n'existe pas.");
    }

    public function context(): array
    {
        return ['maintenance_id' => $this->maintenanceId];
    }
}
