<?php

declare(strict_types=1);

namespace Maintenance\Application\CompleteMaintenance;

final readonly class CompleteMaintenanceCommand
{
    /**
     * @param  array<int, string>  $photos
     */
    public function __construct(
        public string $maintenanceId,
        public ?string $workDescription = null,
        public ?string $partsReplaced = null,
        public ?int $cost = null,
        public array $photos = [],
    ) {}
}
