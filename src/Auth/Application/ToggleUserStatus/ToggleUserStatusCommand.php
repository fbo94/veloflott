<?php

declare(strict_types=1);

namespace Auth\Application\ToggleUserStatus;

/**
 * Command pour activer/désactiver un utilisateur.
 */
final readonly class ToggleUserStatusCommand
{
    public function __construct(
        public string $userId,
        public string $currentUserId,
    ) {
    }
}
