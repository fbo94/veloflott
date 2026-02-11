<?php

declare(strict_types=1);

namespace Rental\Application\ChangeRentalStatus;

final readonly class ChangeRentalStatusResponse
{
    public function __construct(
        public string $rentalId,
        public string $previousStatus,
        public string $newStatus,
        public string $message,
    ) {
    }
}
