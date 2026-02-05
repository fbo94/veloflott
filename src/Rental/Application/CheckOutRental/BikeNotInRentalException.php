<?php

declare(strict_types=1);

namespace Rental\Application\CheckOutRental;

use Shared\Domain\DomainException;

final class BikeNotInRentalException extends DomainException
{
    protected string $errorCode = 'BIKE_NOT_IN_RENTAL';

    public function __construct(
        private readonly string $bikeId,
        private readonly string $rentalId,
    ) {
        parent::__construct("Le vélo '{$bikeId}' ne fait pas partie de la location '{$rentalId}'.");
    }

    public function context(): array
    {
        return [
            'bike_id' => $this->bikeId,
            'rental_id' => $this->rentalId,
        ];
    }
}
