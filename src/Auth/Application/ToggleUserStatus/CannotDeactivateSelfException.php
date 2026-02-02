<?php

declare(strict_types=1);

namespace Auth\Application\ToggleUserStatus;

use Shared\Domain\DomainException;

final class CannotDeactivateSelfException extends DomainException
{
    protected string $errorCode = 'CANNOT_DEACTIVATE_SELF';

    public function __construct()
    {
        parent::__construct('Vous ne pouvez pas désactiver votre propre compte.');
    }
}
