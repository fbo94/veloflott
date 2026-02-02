<?php

declare(strict_types=1);

namespace Rental\Application\CreateRental;

use Shared\Domain\DomainException;

final class CustomerNotFoundException extends DomainException
{
    protected string $errorCode = 'CUSTOMER_NOT_FOUND';

    public function __construct(private readonly string $customerId)
    {
        parent::__construct("Le client '{$customerId}' n'existe pas.");
    }

    public function context(): array
    {
        return ['customer_id' => $this->customerId];
    }
}
