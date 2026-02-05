<?php

declare(strict_types=1);

namespace Fleet\Application\UpdateRate;

use Fleet\Domain\RateDuration;

final readonly class UpdateRateCommand
{
    public function __construct(
        public string $id,
        public RateDuration $duration,
        public float $price,
    ) {
    }
}
