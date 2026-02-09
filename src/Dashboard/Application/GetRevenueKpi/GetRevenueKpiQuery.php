<?php

declare(strict_types=1);

namespace Dashboard\Application\GetRevenueKpi;

final readonly class GetRevenueKpiQuery
{
    public function __construct(
        public ?\DateTimeImmutable $dateFrom = null,
        public ?\DateTimeImmutable $dateTo = null,
    ) {
    }
}
