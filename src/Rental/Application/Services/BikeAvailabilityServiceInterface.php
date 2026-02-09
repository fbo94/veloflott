<?php

declare(strict_types=1);

namespace Rental\Application\Services;

use DateTimeImmutable;

interface BikeAvailabilityServiceInterface
{
    /**
     * Check if a bike is available for a given period
     * Takes into account: rentals (RESERVED, PENDING, ACTIVE) and maintenances (TODO, IN_PROGRESS)
     */
    public function isAvailableForPeriod(
        string $bikeId,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        ?string $excludeRentalId = null,
    ): AvailabilityResult;

    /**
     * Get unavailability slots for a bike in a given period
     *
     * @return UnavailabilitySlot[]
     */
    public function getUnavailabilitySlots(
        string $bikeId,
        DateTimeImmutable $from,
        DateTimeImmutable $to,
    ): array;

    /**
     * Get all available bikes for a given period
     *
     * @return string[] Bike IDs
     */
    public function getAvailableBikesForPeriod(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        ?string $categoryId = null,
        ?string $pricingClassId = null,
    ): array;

    /**
     * Check if a bike is currently physically available (not RENTED, MAINTENANCE, UNAVAILABLE, RETIRED)
     */
    public function isPhysicallyAvailable(string $bikeId): bool;
}
