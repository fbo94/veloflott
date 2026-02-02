<?php

declare(strict_types=1);

namespace Auth\Application\UpdateUserRole;

use Shared\Domain\DomainException;

final class CannotChangeOwnRoleException extends DomainException
{
    protected string $errorCode = 'CANNOT_CHANGE_OWN_ROLE';

    public function __construct()
    {
        parent::__construct('Vous ne pouvez pas modifier votre propre rôle.');
    }
}
