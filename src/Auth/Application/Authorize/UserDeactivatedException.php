<?php

declare(strict_types=1);

namespace Auth\Application\Authorize;

use Shared\Domain\DomainException;

final class UserDeactivatedException extends DomainException
{
    protected string $errorCode = 'USER_DEACTIVATED';

    public function __construct()
    {
        parent::__construct('Votre compte a été désactivé. Veuillez contacter un administrateur.');
    }
}
