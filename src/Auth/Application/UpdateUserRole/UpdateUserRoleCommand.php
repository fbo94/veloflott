<?php

declare(strict_types=1);

namespace Auth\Application\UpdateUserRole;

use Auth\Domain\Role;

/**
 * Command pour changer le rôle d'un utilisateur.
 */
final readonly class UpdateUserRoleCommand
{
    public function __construct(
        public string $userId,
        public Role $newRole,
        public string $currentUserId,
    ) {
    }
}
