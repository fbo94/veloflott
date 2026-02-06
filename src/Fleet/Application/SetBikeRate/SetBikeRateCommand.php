<?php

declare(strict_types=1);

namespace Fleet\Application\SetBikeRate;

final readonly class SetBikeRateCommand
{
    public function __construct(
        public string $bikeId,
        public float $dayPrice,
        public ?float $halfDayPrice = null,
        public ?float $weekendPrice = null,
        public ?float $weekPrice = null,
    ) {}
}
