<?php

declare(strict_types=1);

namespace Rental\Application\CreateRental;

use Fleet\Domain\BikeStatus;
use Shared\Domain\DomainException;

final class BikeNotAvailableException extends DomainException
{
    protected string $errorCode = 'BIKE_NOT_AVAILABLE';

    public function __construct(
        private readonly string $bikeId,
        private readonly BikeStatus $currentStatus,
    ) {
        parent::__construct("Le vélo '{$bikeId}' n'est pas disponible (statut actuel: {$currentStatus->value}).");
    }

    public function context(): array
    {
        return [
            'bike_id' => $this->bikeId,
            'current_status' => $this->currentStatus->value,
        ];
    }
}
