<?php

declare(strict_types=1);

namespace Rental\Infrastructure\Services;

use DateTimeImmutable;
use Fleet\Infrastructure\Persistence\Models\BikeEloquentModel;
use Illuminate\Support\Facades\DB;
use Maintenance\Infrastructure\Persistence\MaintenanceEloquentModel;
use Rental\Application\Services\AvailabilityResult;
use Rental\Application\Services\BikeAvailabilityServiceInterface;
use Rental\Application\Services\UnavailabilitySlot;
use Rental\Infrastructure\Persistence\Models\RentalEloquentModel;
use Rental\Infrastructure\Persistence\Models\RentalItemEloquentModel;

final class EloquentBikeAvailabilityService implements BikeAvailabilityServiceInterface
{
    private const array BLOCKING_RENTAL_STATUSES = ['reserved', 'pending', 'active'];
    private const array BLOCKING_MAINTENANCE_STATUSES = ['todo', 'in_progress'];
    private const array UNAVAILABLE_BIKE_STATUSES = ['rented', 'maintenance', 'unavailable', 'retired'];

    public function isAvailableForPeriod(
        string $bikeId,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        ?string $excludeRentalId = null,
    ): AvailabilityResult {
        // 1. Check bike physical status
        $bike = BikeEloquentModel::find($bikeId);
        if ($bike === null) {
            return AvailabilityResult::unavailable('Bike not found');
        }

        // 2. Get conflicting slots
        $conflictingSlots = $this->getConflictingSlotsForPeriod(
            $bikeId,
            $startDate,
            $endDate,
            $excludeRentalId,
        );

        if (count($conflictingSlots) > 0) {
            $firstSlot = $conflictingSlots[0];
            $reason = $firstSlot->type === 'rental'
                ? 'Bike is already booked for this period'
                : 'Bike has maintenance scheduled for this period';

            return AvailabilityResult::unavailable($reason, $conflictingSlots);
        }

        return AvailabilityResult::available();
    }

    /**
     * @return UnavailabilitySlot[]
     */
    public function getUnavailabilitySlots(
        string $bikeId,
        DateTimeImmutable $from,
        DateTimeImmutable $to,
    ): array {
        $slots = [];

        // Get rental slots
        $rentalSlots = $this->getRentalSlots($bikeId, $from, $to);
        $slots = array_merge($slots, $rentalSlots);

        // Get maintenance slots
        $maintenanceSlots = $this->getMaintenanceSlots($bikeId, $from, $to);
        $slots = array_merge($slots, $maintenanceSlots);

        // Sort by start date
        usort($slots, fn (UnavailabilitySlot $a, UnavailabilitySlot $b) => $a->startDate <=> $b->startDate);

        return $slots;
    }

    /**
     * @return string[]
     */
    public function getAvailableBikesForPeriod(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        ?string $categoryId = null,
        ?string $pricingClassId = null,
    ): array {
        // Start with all available bikes
        $query = BikeEloquentModel::where('status', 'available');

        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }

        if ($pricingClassId !== null) {
            $query->where('pricing_class_id', $pricingClassId);
        }

        $allBikeIds = $query->pluck('id')->all();

        // Get bikes with conflicting rentals
        $bikesWithRentals = $this->getBikesWithConflictingRentals($startDate, $endDate);

        // Get bikes with conflicting maintenances
        $bikesWithMaintenances = $this->getBikesWithConflictingMaintenances($startDate, $endDate);

        // Exclude bikes with conflicts
        $unavailableBikeIds = array_unique(array_merge($bikesWithRentals, $bikesWithMaintenances));

        return array_values(array_diff($allBikeIds, $unavailableBikeIds));
    }

    public function isPhysicallyAvailable(string $bikeId): bool
    {
        $bike = BikeEloquentModel::find($bikeId);

        if ($bike === null) {
            return false;
        }

        return !in_array($bike->status, self::UNAVAILABLE_BIKE_STATUSES, true);
    }

    /**
     * @return UnavailabilitySlot[]
     */
    private function getConflictingSlotsForPeriod(
        string $bikeId,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        ?string $excludeRentalId = null,
    ): array {
        $slots = [];

        // Check rentals
        $rentalSlots = $this->getRentalSlots($bikeId, $startDate, $endDate, $excludeRentalId);
        foreach ($rentalSlots as $slot) {
            if ($slot->overlaps($startDate, $endDate)) {
                $slots[] = $slot;
            }
        }

        // Check maintenances
        $maintenanceSlots = $this->getMaintenanceSlots($bikeId, $startDate, $endDate);
        foreach ($maintenanceSlots as $slot) {
            if ($slot->overlaps($startDate, $endDate)) {
                $slots[] = $slot;
            }
        }

        return $slots;
    }

    /**
     * @return UnavailabilitySlot[]
     */
    private function getRentalSlots(
        string $bikeId,
        DateTimeImmutable $from,
        DateTimeImmutable $to,
        ?string $excludeRentalId = null,
    ): array {
        $query = RentalEloquentModel::query()
            ->whereIn('status', self::BLOCKING_RENTAL_STATUSES)
            ->where(function ($q) use ($from, $to) {
                // Overlap condition: rental.start < period.end AND rental.end > period.start
                $q->where('start_date', '<', $to->format('Y-m-d H:i:s'))
                    ->where('expected_return_date', '>', $from->format('Y-m-d H:i:s'));
            })
            ->whereHas('items', function ($q) use ($bikeId) {
                $q->where('bike_id', $bikeId);
            });

        if ($excludeRentalId !== null) {
            $query->where('id', '!=', $excludeRentalId);
        }

        $rentals = $query->get();

        return $rentals->map(function (RentalEloquentModel $rental) {
            return UnavailabilitySlot::fromRental(
                rentalId: (string) $rental->id,
                startDate: $rental->start_date,
                endDate: $rental->expected_return_date,
                status: $rental->status,
            );
        })->all();
    }

    /**
     * @return UnavailabilitySlot[]
     */
    private function getMaintenanceSlots(
        string $bikeId,
        DateTimeImmutable $from,
        DateTimeImmutable $to,
    ): array {
        $maintenances = MaintenanceEloquentModel::query()
            ->where('bike_id', $bikeId)
            ->whereIn('status', self::BLOCKING_MAINTENANCE_STATUSES)
            ->where(function ($q) use ($from, $to) {
                // For maintenances, we use scheduled_at as start
                // and completed_at (or scheduled_at + 1 day) as end
                $q->where('scheduled_at', '<', $to->format('Y-m-d H:i:s'))
                    ->where(function ($q2) use ($from) {
                        $q2->where('completed_at', '>', $from->format('Y-m-d H:i:s'))
                            ->orWhereNull('completed_at');
                    });
            })
            ->get();

        return $maintenances->map(function (MaintenanceEloquentModel $maintenance) {
            $scheduledAt = $maintenance->scheduled_at instanceof DateTimeImmutable
                ? $maintenance->scheduled_at
                : new DateTimeImmutable($maintenance->scheduled_at);

            $completedAt = $maintenance->completed_at
                ? ($maintenance->completed_at instanceof DateTimeImmutable
                    ? $maintenance->completed_at
                    : new DateTimeImmutable($maintenance->completed_at))
                : null;

            return UnavailabilitySlot::fromMaintenance(
                maintenanceId: $maintenance->id,
                scheduledAt: $scheduledAt,
                completedAt: $completedAt,
                reason: $maintenance->reason ?? 'maintenance',
            );
        })->all();
    }

    /**
     * @return string[]
     */
    private function getBikesWithConflictingRentals(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
    ): array {
        return RentalItemEloquentModel::query()
            ->select('bike_id')
            ->distinct()
            ->whereHas('rental', function ($q) use ($startDate, $endDate) {
                $q->whereIn('status', self::BLOCKING_RENTAL_STATUSES)
                    ->where('start_date', '<', $endDate->format('Y-m-d H:i:s'))
                    ->where('expected_return_date', '>', $startDate->format('Y-m-d H:i:s'));
            })
            ->pluck('bike_id')
            ->all();
    }

    /**
     * @return string[]
     */
    private function getBikesWithConflictingMaintenances(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
    ): array {
        return MaintenanceEloquentModel::query()
            ->select('bike_id')
            ->distinct()
            ->whereIn('status', self::BLOCKING_MAINTENANCE_STATUSES)
            ->where('scheduled_at', '<', $endDate->format('Y-m-d H:i:s'))
            ->where(function ($q) use ($startDate) {
                $q->where('completed_at', '>', $startDate->format('Y-m-d H:i:s'))
                    ->orWhereNull('completed_at');
            })
            ->pluck('bike_id')
            ->all();
    }
}
