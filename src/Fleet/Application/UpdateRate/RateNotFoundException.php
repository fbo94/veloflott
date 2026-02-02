<?php

declare(strict_types=1);

namespace Fleet\Application\UpdateRate;

use Shared\Domain\DomainException;

final class RateNotFoundException extends DomainException
{
    protected string $errorCode = 'RATE_NOT_FOUND';

    public function __construct(private readonly string $rateId)
    {
        parent::__construct("Le tarif '{$rateId}' n'existe pas.");
    }

    public function context(): array
    {
        return ['rate_id' => $this->rateId];
    }
}
