<?php

declare(strict_types=1);

namespace Rental\Application\CheckInRental;

/**
 * Réponse après le check-in d'une location.
 */
final readonly class CheckInRentalResponse
{
    public function __construct(
        public string $rentalId,
        public string $message,
    ) {
    }
}
