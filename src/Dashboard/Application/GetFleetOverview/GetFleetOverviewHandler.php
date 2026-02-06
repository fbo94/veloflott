<?php

declare(strict_types=1);

namespace Dashboard\Application\GetFleetOverview;

use Customer\Domain\CustomerRepositoryInterface;
use Fleet\Domain\BikeRepositoryInterface;
use Maintenance\Domain\MaintenanceRepositoryInterface;
use Rental\Domain\RentalRepositoryInterface;

final readonly class GetFleetOverviewHandler
{
    public function __construct(
        private BikeRepositoryInterface $bikeRepository,
        private RentalRepositoryInterface $rentalRepository,
        private MaintenanceRepositoryInterface $maintenanceRepository,
        private CustomerRepositoryInterface $customerRepository,
    ) {}

    public function handle(): GetFleetOverviewResponse
    {
        // Compteurs flotte
        $bikesByStatus = $this->bikeRepository->countByStatus();
        $totalBikes = array_sum($bikesByStatus);
        $activeBikes = $this->bikeRepository->countActive();
        $averageAge = $this->bikeRepository->getAverageAge();

        // Compteurs locations
        $activeRentals = $this->rentalRepository->countActive();

        // Compteurs maintenances
        $maintenancesByStatus = $this->maintenanceRepository->countByStatus();

        // Compteurs clients
        $totalCustomers = $this->customerRepository->count();

        return new GetFleetOverviewResponse(
            fleetSummary: [
                'total_bikes' => $totalBikes,
                'active_bikes' => $activeBikes,
                'average_age_years' => $averageAge,
                'by_status' => $bikesByStatus,
            ],
            rentalsSummary: [
                'active_rentals' => $activeRentals,
            ],
            maintenanceSummary: [
                'by_status' => $maintenancesByStatus,
                'urgent_pending' => $maintenancesByStatus['todo'] ?? 0, // À affiner avec priorité
            ],
            customersSummary: [
                'total_customers' => $totalCustomers,
                'with_active_rental' => $activeRentals, // 1 rental = 1 customer
            ],
        );
    }
}
