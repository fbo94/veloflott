<?php

declare(strict_types=1);

namespace Fleet\Application\GetBikeStatusHistory;

final readonly class GetBikeStatusHistoryQuery
{
    public function __construct(
        public string $bikeId,
    ) {
    }
}
