<?php

declare(strict_types=1);

namespace Rental\Application\EarlyReturn;

use Rental\Domain\RentalStatus;

final class CannotEarlyReturnException extends \DomainException
{
    public function __construct(
        public readonly string $rentalId,
        public readonly RentalStatus $currentStatus,
    ) {
        parent::__construct(
            sprintf(
                'Cannot early return rental %s: rental is in %s status (must be ACTIVE)',
                $rentalId,
                $currentStatus->value,
            ),
        );
    }
}
