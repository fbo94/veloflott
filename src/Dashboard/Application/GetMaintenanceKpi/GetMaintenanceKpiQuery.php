<?php

declare(strict_types=1);

namespace Dashboard\Application\GetMaintenanceKpi;

final readonly class GetMaintenanceKpiQuery
{
    public function __construct(
        public ?\DateTimeImmutable $dateFrom = null,
        public ?\DateTimeImmutable $dateTo = null,
    ) {
    }
}
