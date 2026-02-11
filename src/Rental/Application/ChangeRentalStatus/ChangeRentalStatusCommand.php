<?php

declare(strict_types=1);

namespace Rental\Application\ChangeRentalStatus;

use Rental\Domain\RentalStatus;

final readonly class ChangeRentalStatusCommand
{
    public function __construct(
        public string $rentalId,
        public RentalStatus $newStatus,
        public ?string $reason = null,
    ) {
    }
}
