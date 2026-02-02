<?php

declare(strict_types=1);

namespace Auth\Application\GetCurrentUser;

use Shared\Domain\DomainException;

final class UserNotFoundException extends DomainException
{
    protected string $errorCode = 'USER_NOT_FOUND';

    public function __construct(private readonly string $userId)
    {
        parent::__construct("L'utilisateur '{$userId}' n'existe pas.");
    }

    public function context(): array
    {
        return ['user_id' => $this->userId];
    }
}
