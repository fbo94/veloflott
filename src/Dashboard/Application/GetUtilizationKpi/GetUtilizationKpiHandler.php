<?php

declare(strict_types=1);

namespace Dashboard\Application\GetUtilizationKpi;

use Fleet\Domain\BikeRepositoryInterface;
use Rental\Domain\RentalRepositoryInterface;

final readonly class GetUtilizationKpiHandler
{
    public function __construct(
        private BikeRepositoryInterface $bikeRepository,
        private RentalRepositoryInterface $rentalRepository,
    ) {}

    public function handle(GetUtilizationKpiQuery $query): GetUtilizationKpiResponse
    {
        // Par défaut : 30 derniers jours
        $dateTo = $query->dateTo ?? new \DateTimeImmutable;
        $dateFrom = $query->dateFrom ?? $dateTo->modify('-30 days');

        // Compteurs de base
        $bikesByStatus = $this->bikeRepository->countByStatus();

        // Calcul taux d'utilisation
        $rentedBikes = $bikesByStatus['rented'] ?? 0;
        $availableBikes = $bikesByStatus['available'] ?? 0;
        $totalRentableBikes = $rentedBikes + $availableBikes;

        $utilizationRate = $totalRentableBikes > 0
            ? round(($rentedBikes / $totalRentableBikes) * 100, 1)
            : 0.0;

        // Durée moyenne de location
        $averageRentalDuration = $this->rentalRepository->getAverageRentalDurationHours();

        // Nombre de locations dans la période
        $rentalCount = $this->rentalRepository->countByPeriod($dateFrom, $dateTo);

        return new GetUtilizationKpiResponse(
            period: [
                'from' => $dateFrom->format('Y-m-d'),
                'to' => $dateTo->format('Y-m-d'),
                'days' => $dateFrom->diff($dateTo)->days + 1,
            ],
            utilizationRate: $utilizationRate,
            rentedBikes: $rentedBikes,
            availableBikes: $availableBikes,
            totalRentableBikes: $totalRentableBikes,
            avgRentalDurationHours: $averageRentalDuration,
            rentalCountInPeriod: $rentalCount,
        );
    }
}
