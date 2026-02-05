<?php

declare(strict_types=1);

namespace Dashboard\Application\GetUtilizationKpi;

final readonly class GetUtilizationKpiQuery
{
    public function __construct(
        public ?\DateTimeImmutable $dateFrom = null,
        public ?\DateTimeImmutable $dateTo = null,
    ) {
    }
}
