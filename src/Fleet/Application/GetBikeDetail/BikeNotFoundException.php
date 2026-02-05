<?php

declare(strict_types=1);

namespace Fleet\Application\GetBikeDetail;

use Shared\Domain\DomainException;

final class BikeNotFoundException extends DomainException
{
    protected string $errorCode = 'BIKE_NOT_FOUND';

    public function __construct(private readonly string $bikeId)
    {
        parent::__construct("Le vélo '{$bikeId}' n'existe pas.");
    }

    public function context(): array
    {
        return ['bike_id' => $this->bikeId];
    }
}
