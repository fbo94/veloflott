<?php

declare(strict_types=1);

namespace Auth\Application\ListUsers;

use Auth\Domain\Role;

/**
 * Query pour lister les utilisateurs.
 */
final readonly class ListUsersQuery
{
    public function __construct(
        public ?Role $role = null,
        public ?bool $isActive = null,
    ) {
    }
}
