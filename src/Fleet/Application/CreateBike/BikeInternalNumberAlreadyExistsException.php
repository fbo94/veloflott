<?php

declare(strict_types=1);

namespace Fleet\Application\CreateBike;

use Shared\Domain\DomainException;

final class BikeInternalNumberAlreadyExistsException extends DomainException
{
    protected string $errorCode = 'BIKE_INTERNAL_NUMBER_ALREADY_EXISTS';

    public function __construct(private readonly string $internalNumber)
    {
        parent::__construct("Un vélo avec le numéro interne '{$internalNumber}' existe déjà.");
    }

    public function context(): array
    {
        return ['internal_number' => $this->internalNumber];
    }
}
