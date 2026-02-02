<?php

declare(strict_types=1);

namespace Auth\Application\Authorize;

use Shared\Domain\DomainException;

final class InvalidAuthorizationCodeException extends DomainException
{
    protected string $errorCode = 'INVALID_AUTHORIZATION_CODE';

    public function __construct(string $message = 'Le code d\'autorisation est invalide ou expiré.')
    {
        parent::__construct($message);
    }
}
