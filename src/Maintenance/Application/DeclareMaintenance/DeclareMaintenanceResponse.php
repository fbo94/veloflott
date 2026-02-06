<?php

declare(strict_types=1);

namespace Maintenance\Application\DeclareMaintenance;

final readonly class DeclareMaintenanceResponse
{
    public function __construct(
        public string $maintenanceId,
        public string $bikeId,
        public string $message,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->maintenanceId,
            'bike_id' => $this->bikeId,
            'message' => $this->message,
        ];
    }
}
