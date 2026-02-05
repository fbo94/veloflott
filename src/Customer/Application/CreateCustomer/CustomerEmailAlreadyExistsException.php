<?php

declare(strict_types=1);

namespace Customer\Application\CreateCustomer;

use Shared\Domain\DomainException;

final class CustomerEmailAlreadyExistsException extends DomainException
{
    protected string $errorCode = 'CUSTOMER_EMAIL_ALREADY_EXISTS';

    public function __construct(private readonly string $email)
    {
        parent::__construct("Un client avec l'email '{$email}' existe déjà.");
    }

    public function context(): array
    {
        return ['email' => $this->email];
    }
}
