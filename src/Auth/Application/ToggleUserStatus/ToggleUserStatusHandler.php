<?php

declare(strict_types=1);

namespace Auth\Application\ToggleUserStatus;

use Auth\Application\GetCurrentUser\UserNotFoundException;
use Auth\Domain\UserRepositoryInterface;

final class ToggleUserStatusHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {
    }

    /**
     * @throws CannotDeactivateSelfException
     * @throws UserNotFoundException
     */
    public function handle(ToggleUserStatusCommand $command): void
    {
        // Empêcher de se désactiver soi-même
        if ($command->userId === $command->currentUserId) {
            throw new CannotDeactivateSelfException();
        }

        $user = $this->users->findById($command->userId);

        if ($user === null) {
            throw new UserNotFoundException($command->userId);
        }

        if ($user->isActive()) {
            $user->deactivate();
        } else {
            $user->activate();
        }

        $this->users->save($user);
    }
}
