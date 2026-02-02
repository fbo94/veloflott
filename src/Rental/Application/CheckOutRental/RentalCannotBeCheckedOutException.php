<?php

declare(strict_types=1);

namespace Rental\Application\CheckOutRental;

use Rental\Domain\RentalStatus;
use Shared\Domain\DomainException;

final class RentalCannotBeCheckedOutException extends DomainException
{
    protected string $errorCode = 'RENTAL_CANNOT_BE_CHECKED_OUT';

    public function __construct(
        private readonly string $rentalId,
        private readonly RentalStatus $currentStatus,
    ) {
        parent::__construct("La location '{$rentalId}' ne peut pas être clôturée (statut actuel: {$currentStatus->value}).");
    }

    public function context(): array
    {
        return [
            'rental_id' => $this->rentalId,
            'current_status' => $this->currentStatus->value,
        ];
    }
}
