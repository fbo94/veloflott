<?php

declare(strict_types=1);

namespace Rental\Application\CancelRental;

final class RentalNotFoundException extends \Exception
{
    public function __construct(string $rentalId)
    {
        parent::__construct("Rental not found: {$rentalId}");
    }
}
