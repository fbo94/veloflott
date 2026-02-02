<?php

declare(strict_types=1);

namespace Rental\Application\CheckOutRental;

final readonly class CheckOutRentalCommand
{
    /**
     * @param BikeConditionData[] $bikesCondition
     */
    public function __construct(
        public string $rentalId,
        public \DateTimeImmutable $actualReturnDate,
        public array $bikesCondition,
        public float $depositRetained = 0.0,
        public ?float $hourlyLateRate = 10.0,
    ) {}
}
