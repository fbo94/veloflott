<?php

declare(strict_types=1);

namespace Rental\Application\CancelRental;

final readonly class CancelRentalCommand
{
    public function __construct(
        public string $rentalId,
        public string $cancellationReason,
    ) {}
}
