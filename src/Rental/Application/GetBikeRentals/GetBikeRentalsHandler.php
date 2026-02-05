<?php

declare(strict_types=1);

namespace Rental\Application\GetBikeRentals;

use Rental\Domain\RentalRepositoryInterface;
use Rental\Domain\RentalStatus;

final readonly class GetBikeRentalsHandler
{
    public function __construct(
        private RentalRepositoryInterface $rentalRepository,
    ) {
    }

    public function handle(GetBikeRentalsQuery $query): GetBikeRentalsResponse
    {
        // Déterminer les statuts à filtrer en fonction du filtre
        $statuses = $this->getStatusesFromFilter($query->filter);

        // Récupérer les locations du vélo
        $rentals = $this->rentalRepository->findByBikeId($query->bikeId, $statuses);

        // Convertir en DTOs
        $rentalDtos = array_map(
            fn ($rental) => BikeRentalDto::fromDomain($rental),
            $rentals
        );

        return new GetBikeRentalsResponse(
            bikeId: $query->bikeId,
            rentals: $rentalDtos,
            totalCount: count($rentalDtos),
        );
    }

    /**
     * @return RentalStatus[]|null
     */
    private function getStatusesFromFilter(?string $filter): ?array
    {
        return match ($filter) {
            'past' => [RentalStatus::COMPLETED, RentalStatus::CANCELLED],
            'current' => [RentalStatus::ACTIVE],
            'upcoming' => [RentalStatus::PENDING],
            'all', null => null,
            default => throw new \InvalidArgumentException("Invalid filter: {$filter}. Allowed values: all, past, current, upcoming"),
        };
    }
}
