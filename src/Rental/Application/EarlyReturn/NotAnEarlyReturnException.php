<?php

declare(strict_types=1);

namespace Rental\Application\EarlyReturn;

use DateTimeImmutable;

final class NotAnEarlyReturnException extends \DomainException
{
    public function __construct(
        public readonly string $rentalId,
        public readonly DateTimeImmutable $actualReturnDate,
        public readonly DateTimeImmutable $expectedReturnDate,
    ) {
        parent::__construct(
            sprintf(
                'Rental %s: return date %s is not before expected return date %s. Use regular checkout instead.',
                $rentalId,
                $actualReturnDate->format('Y-m-d H:i'),
                $expectedReturnDate->format('Y-m-d H:i'),
            ),
        );
    }
}
