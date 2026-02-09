<?php

declare(strict_types=1);

namespace Rental\Application\CreateReservation;

use DateTimeImmutable;

final class BikeNotAvailableForPeriodException extends \DomainException
{
    public function __construct(
        public readonly string $bikeId,
        public readonly DateTimeImmutable $startDate,
        public readonly DateTimeImmutable $endDate,
        string $reason,
    ) {
        parent::__construct(
            sprintf(
                'Bike %s is not available from %s to %s: %s',
                $bikeId,
                $startDate->format('Y-m-d H:i'),
                $endDate->format('Y-m-d H:i'),
                $reason,
            ),
        );
    }
}
