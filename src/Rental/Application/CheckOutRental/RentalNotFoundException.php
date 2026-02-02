<?php

declare(strict_types=1);

namespace Rental\Application\CheckOutRental;

use Shared\Domain\DomainException;

final class RentalNotFoundException extends DomainException
{
    protected string $errorCode = 'RENTAL_NOT_FOUND';

    public function __construct(private readonly string $rentalId)
    {
        parent::__construct("La location '{$rentalId}' n'existe pas.");
    }

    public function context(): array
    {
        return ['rental_id' => $this->rentalId];
    }
}
