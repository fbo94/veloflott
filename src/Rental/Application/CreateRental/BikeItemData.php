<?php

declare(strict_types=1);

namespace Rental\Application\CreateRental;

final readonly class BikeItemData
{
    public function __construct(
        public string $bikeId,
        public float $dailyRate,
        public int $quantity = 1,
    ) {
    }
}
