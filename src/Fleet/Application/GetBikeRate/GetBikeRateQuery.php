<?php

declare(strict_types=1);

namespace Fleet\Application\GetBikeRate;

final readonly class GetBikeRateQuery
{
    public function __construct(
        public string $bikeId,
    ) {}
}
