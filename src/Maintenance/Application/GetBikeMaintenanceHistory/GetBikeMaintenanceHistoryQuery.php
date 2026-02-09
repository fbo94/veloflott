<?php

declare(strict_types=1);

namespace Maintenance\Application\GetBikeMaintenanceHistory;

final readonly class GetBikeMaintenanceHistoryQuery
{
    public function __construct(
        public string $bikeId,
    ) {
    }
}
