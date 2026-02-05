<?php

declare(strict_types=1);

namespace Auth\Application\ListUsers;

use Auth\Domain\UserRepositoryInterface;

final class ListUsersHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {
    }

    public function handle(ListUsersQuery $query): ListUsersResponse
    {
        $users = $this->users->findAll($query->role, $query->isActive);

        return ListUsersResponse::fromUsers($users);
    }
}
