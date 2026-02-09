<?php

declare(strict_types=1);

namespace Dashboard\Application\GetTopBikes;

use Fleet\Domain\BikeRepositoryInterface;
use Rental\Domain\RentalRepositoryInterface;

final readonly class GetTopBikesHandler
{
    public function __construct(
        private RentalRepositoryInterface $rentalRepository,
        private BikeRepositoryInterface $bikeRepository,
    ) {
    }

    public function handle(GetTopBikesQuery $query): GetTopBikesResponse
    {
        // Récupérer les statistiques par vélo
        $stats = $this->rentalRepository->getStatsByBike($query->limit);

        // Enrichir avec les informations du vélo
        $enrichedStats = array_map(function ($stat) {
            $bike = $this->bikeRepository->findById($stat['bike_id']);

            return [
                'bike_id' => $stat['bike_id'],
                'internal_number' => $bike?->internalNumber() ?? 'N/A',
                'rental_count' => $stat['rental_count'],
                'total_revenue_cents' => $stat['total_revenue'],
                'total_revenue_formatted' => number_format($stat['total_revenue'] / 100, 2, '.', ' ') . ' EUR',
            ];
        }, $stats);

        return new GetTopBikesResponse(
            topBikes: $enrichedStats,
            limit: $query->limit,
        );
    }
}
