<?php

declare(strict_types=1);

namespace Auth\Application\GetCurrentUser;

use Auth\Domain\UserRepositoryInterface;

final class GetCurrentUserHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {
    }

    /**
     * @throws UserNotFoundException
     */
    public function handle(GetCurrentUserQuery $query): GetCurrentUserResponse
    {
        $user = $this->users->findById($query->userId);

        if ($user === null) {
            throw new UserNotFoundException($query->userId);
        }

        return GetCurrentUserResponse::fromUser($user);
    }
}
