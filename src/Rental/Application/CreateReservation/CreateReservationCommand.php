<?php

declare(strict_types=1);

namespace Rental\Application\CreateReservation;

use DateTimeImmutable;
use Rental\Application\CreateRental\BikeItemData;
use Rental\Application\CreateRental\EquipmentItemData;
use Rental\Domain\RentalDuration;

final readonly class CreateReservationCommand
{
    /**
     * @param BikeItemData[] $bikeItems
     * @param EquipmentItemData[] $equipmentItems
     */
    public function __construct(
        public string $customerId,
        public DateTimeImmutable $startDate,
        public RentalDuration $duration,
        public ?DateTimeImmutable $customEndDate,
        public float $depositAmount,
        public array $bikeItems,
        public array $equipmentItems = [],
        public ?string $tenantId = null,
        public ?string $siteId = null,
    ) {
    }
}
