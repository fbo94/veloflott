<?php

declare(strict_types=1);

namespace Dashboard\Application\GetTodayActivity;

use Maintenance\Domain\MaintenanceRepositoryInterface;
use Rental\Domain\RentalRepositoryInterface;

final readonly class GetTodayActivityHandler
{
    public function __construct(
        private RentalRepositoryInterface $rentalRepository,
        private MaintenanceRepositoryInterface $maintenanceRepository,
    ) {
    }

    public function handle(GetTodayActivityQuery $query): GetTodayActivityResponse
    {
        // Par défaut : aujourd'hui
        $date = $query->date ?? new \DateTimeImmutable();

        // Locations démarrées aujourd'hui
        $rentalsStartedToday = $this->rentalRepository->findStartedOnDate($date);

        // Retours prévus aujourd'hui
        $scheduledReturnsToday = $this->rentalRepository->findExpectedReturnOnDate($date);

        // Retours en retard
        $lateRentals = $this->rentalRepository->findLateRentals();

        // Maintenances planifiées aujourd'hui
        $maintenancesScheduledToday = $this->maintenanceRepository->findScheduledOnDate($date);

        // Maintenances complétées aujourd'hui
        $maintenancesCompletedToday = $this->maintenanceRepository->findCompletedOnDate($date);

        // Maintenances urgentes en attente
        $urgentPendingCount = $this->maintenanceRepository->countUrgentPending();

        return new GetTodayActivityResponse(
            date: $date->format('Y-m-d'),
            rentalsStartedToday: count($rentalsStartedToday),
            scheduledReturnsToday: count($scheduledReturnsToday),
            lateReturns: count($lateRentals),
            recentRentals: array_slice(
                array_map(
                    fn($rental) => [
                        'id' => $rental->id(),
                        'customer_id' => $rental->customerId(),
                        'start_date' => $rental->startDate()->format('Y-m-d H:i:s'),
                        'expected_return_date' => $rental->expectedReturnDate()->format('Y-m-d H:i:s'),
                        'status' => $rental->status()->value,
                        'total_amount' => $rental->totalAmount(),
                    ],
                    $rentalsStartedToday
                ),
                0,
                5
            ),
            maintenancesScheduledToday: count($maintenancesScheduledToday),
            maintenancesCompletedToday: count($maintenancesCompletedToday),
            urgentPendingMaintenances: $urgentPendingCount,
        );
    }
}
