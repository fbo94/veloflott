<?php

declare(strict_types=1);

namespace Auth\Application\UpdateUserRole;

use Auth\Application\GetCurrentUser\UserNotFoundException;
use Auth\Domain\UserRepositoryInterface;

final class UpdateUserRoleHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    /**
     * @throws UserNotFoundException
     * @throws CannotChangeOwnRoleException
     */
    public function handle(UpdateUserRoleCommand $command): void
    {
        // Empêcher de modifier son propre rôle
        if ($command->userId === $command->currentUserId) {
            throw new CannotChangeOwnRoleException();
        }

        $user = $this->users->findById($command->userId);

        if ($user === null) {
            throw new UserNotFoundException($command->userId);
        }

        $user->changeRole($command->newRole);

        $this->users->save($user);
    }
}
