<?php

declare(strict_types=1);

namespace Rental\Application\CreateRental;

use Fleet\Domain\BikeStatus;
use Shared\Domain\DomainException;

final class BikeNotAvailableException extends DomainException
{
    protected string $errorCode = 'BIKE_NOT_AVAILABLE';

    private readonly string $statusValue;

    public function __construct(
        private readonly string $bikeId,
        BikeStatus|string $currentStatus,
    ) {
        $this->statusValue = $currentStatus instanceof BikeStatus
            ? $currentStatus->value
            : $currentStatus;

        parent::__construct("Le vélo '{$bikeId}' n'est pas disponible (statut actuel: {$this->statusValue}).");
    }

    public function context(): array
    {
        return [
            'bike_id' => $this->bikeId,
            'current_status' => $this->statusValue,
        ];
    }
}
