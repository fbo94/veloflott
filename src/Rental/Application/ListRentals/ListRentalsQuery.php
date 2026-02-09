<?php

declare(strict_types=1);

namespace Rental\Application\ListRentals;

final readonly class ListRentalsQuery
{
    public function __construct(
        public ?string $customerId = null,
        public ?string $status = null,
        public ?string $startDate = null,
        public ?string $endDate = null,
        public int $page = 1,
        public int $perPage = 20,
    ) {
    }
}
