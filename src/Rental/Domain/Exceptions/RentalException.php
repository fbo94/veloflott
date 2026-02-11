<?php

declare(strict_types=1);

namespace Rental\Domain\Exceptions;

use DomainException;

final class RentalException extends DomainException
{
    public static function notFound(string $rentalId): self
    {
        return new self("Rental with ID {$rentalId} not found", 404);
    }

    public static function customerNotFound(string $customerId): self
    {
        return new self("Customer with ID {$customerId} not found", 404);
    }

    public static function bikeNotFound(string $bikeId): self
    {
        return new self("Bike with ID {$bikeId} not found", 404);
    }

    public static function bikeNotAvailable(string $bikeId): self
    {
        return new self("Bike with ID {$bikeId} is not available for rental", 400);
    }

    public static function cannotCheckIn(string $rentalId, string $reason): self
    {
        return new self("Cannot check-in rental {$rentalId}: {$reason}", 400);
    }

    public static function cannotCheckOut(string $rentalId, string $reason): self
    {
        return new self("Cannot check-out rental {$rentalId}: {$reason}", 400);
    }

    public static function bikeNotInRental(string $rentalId, string $bikeId): self
    {
        return new self("Bike {$bikeId} is not part of rental {$rentalId}", 400);
    }

    public static function invalidStatusTransition(string $from, string $to): self
    {
        return new self("Invalid status transition from '{$from}' to '{$to}'", 400);
    }
}
