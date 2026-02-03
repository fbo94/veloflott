<?php

declare(strict_types=1);

namespace Dashboard\Application\GetRevenueKpi;

use Fleet\Domain\BikeRepositoryInterface;
use Rental\Domain\RentalRepositoryInterface;

final readonly class GetRevenueKpiHandler
{
    public function __construct(
        private BikeRepositoryInterface $bikeRepository,
        private RentalRepositoryInterface $rentalRepository,
    ) {
    }

    public function handle(GetRevenueKpiQuery $query): GetRevenueKpiResponse
    {
        // Par défaut : 30 derniers jours
        $dateTo = $query->dateTo ?? new \DateTimeImmutable();
        $dateFrom = $query->dateFrom ?? $dateTo->modify('-30 days');

        // Nombre de vélos actifs
        $activeBikes = $this->bikeRepository->countActive();

        // KPIs Location
        $rentalCount = $this->rentalRepository->countByPeriod($dateFrom, $dateTo);
        $totalRevenue = $this->rentalRepository->sumRevenueByPeriod($dateFrom, $dateTo);

        // Revenue per available vehicle (RevPAV)
        $revpav = $activeBikes > 0
            ? (int) round($totalRevenue / $activeBikes)
            : 0;

        // Revenu moyen par location
        $avgRevenuePerRental = $rentalCount > 0
            ? (int) round($totalRevenue / $rentalCount)
            : 0;

        return new GetRevenueKpiResponse(
            period: [
                'from' => $dateFrom->format('Y-m-d'),
                'to' => $dateTo->format('Y-m-d'),
                'days' => $dateFrom->diff($dateTo)->days + 1,
            ],
            totalRevenueCents: $totalRevenue,
            totalRevenueFormatted: number_format($totalRevenue / 100, 2, '.', ' ') . ' EUR',
            revpavCents: $revpav,
            avgRevenuePerRentalCents: $avgRevenuePerRental,
            rentalCount: $rentalCount,
            activeBikes: $activeBikes,
        );
    }
}
