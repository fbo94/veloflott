<?php

declare(strict_types=1);

namespace Maintenance\Application\ListMaintenances;

final readonly class ListMaintenancesQuery
{
    public function __construct(
        public ?string $bikeId = null,
        public ?string $status = null,
        public ?string $priority = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
    ) {}
}
