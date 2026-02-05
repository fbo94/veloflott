<?php

declare(strict_types=1);

namespace Rental\Application\ListActiveRentals;

final readonly class ListActiveRentalsResponse
{
    /**
     * @param ActiveRentalDto[] $rentals
     */
    public function __construct(
        public array $rentals,
    ) {
    }

    public function toArray(): array
    {
        return [
            'data' => array_map(
                fn (ActiveRentalDto $rental) => $rental->toArray(),
                $this->rentals
            ),
            'total' => count($this->rentals),
        ];
    }
}
