<?php

declare(strict_types=1);

namespace Rental\Application\ListActiveRentals;

final readonly class ListActiveRentalsQuery
{
    public function __construct(
        public ?string $customerId = null,
        public ?string $bikeId = null,
        public bool $onlyLate = false,
    ) {}
}
