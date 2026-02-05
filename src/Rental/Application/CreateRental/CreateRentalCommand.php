<?php

declare(strict_types=1);

namespace Rental\Application\CreateRental;

use Rental\Domain\RentalDuration;

final readonly class CreateRentalCommand
{
    /**
     * @param BikeItemData[] $bikeItems
     * @param EquipmentItemData[] $equipmentItems
     */
    public function __construct(
        public string $customerId,
        public \DateTimeImmutable $startDate,
        public RentalDuration $duration,
        public ?\DateTimeImmutable $customEndDate, // Pour duration CUSTOM
        public float $depositAmount,
        public array $bikeItems,
        public array $equipmentItems,
    ) {}
}
