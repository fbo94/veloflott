<?php

declare(strict_types=1);

namespace Rental\Application\CheckOutRental;

use Rental\Domain\BikeCondition;

final readonly class BikeConditionData
{
    /**
     * @param string[] $damagePhotos
     */
    public function __construct(
        public string $bikeId,
        public BikeCondition $condition,
        public ?string $damageDescription = null,
        public ?array $damagePhotos = null,
    ) {
    }
}
