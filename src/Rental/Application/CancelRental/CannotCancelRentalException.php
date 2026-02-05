<?php

declare(strict_types=1);

namespace Rental\Application\CancelRental;

final class CannotCancelRentalException extends \Exception
{
    public function __construct(string $reason)
    {
        parent::__construct("Cannot cancel rental: {$reason}");
    }
}
