<?php

declare(strict_types=1);

namespace Rental\Application\GetBikeRentals;

final readonly class GetBikeRentalsResponse
{
    /**
     * @param  BikeRentalDto[]  $rentals
     */
    public function __construct(
        public string $bikeId,
        public array $rentals,
        public int $totalCount,
    ) {}

    public function toArray(): array
    {
        return [
            'bike_id' => $this->bikeId,
            'total_count' => $this->totalCount,
            'rentals' => array_map(fn (BikeRentalDto $rental) => $rental->toArray(), $this->rentals),
        ];
    }
}
