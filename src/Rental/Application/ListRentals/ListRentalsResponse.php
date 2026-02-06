<?php

declare(strict_types=1);

namespace Rental\Application\ListRentals;

final readonly class ListRentalsResponse
{
    /**
     * @param  RentalDto[]  $rentals
     */
    public function __construct(
        public array $rentals,
        public int $total,
        public int $currentPage,
        public int $perPage,
        public int $lastPage,
    ) {}

    public function toArray(): array
    {
        return [
            'data' => array_map(fn ($rental) => $rental->toArray(), $this->rentals),
            'meta' => [
                'total' => $this->total,
                'current_page' => $this->currentPage,
                'per_page' => $this->perPage,
                'last_page' => $this->lastPage,
            ],
        ];
    }
}
