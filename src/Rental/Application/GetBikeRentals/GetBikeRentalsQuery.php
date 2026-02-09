<?php

declare(strict_types=1);

namespace Rental\Application\GetBikeRentals;

final readonly class GetBikeRentalsQuery
{
    /**
     * @param  string|null  $filter  Filtre: 'all', 'past', 'current', 'upcoming' (null = all)
     */
    public function __construct(
        public string $bikeId,
        public ?string $filter = null,
    ) {
    }
}
