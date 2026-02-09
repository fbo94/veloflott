<?php

declare(strict_types=1);

namespace Rental\Application\CheckInRental;

/**
 * Commande pour effectuer le check-in d'une location.
 */
final readonly class CheckInRentalCommand
{
    /**
     * @param  BikeCheckInData[]  $bikesCheckIn
     */
    public function __construct(
        public string $rentalId,
        public array $bikesCheckIn,
        public ?string $customerSignature = null,
    ) {
    }
}
