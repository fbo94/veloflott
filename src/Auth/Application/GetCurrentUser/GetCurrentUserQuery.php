<?php

declare(strict_types=1);

namespace Auth\Application\GetCurrentUser;

/**
 * Query pour récupérer l'utilisateur courant.
 */
final readonly class GetCurrentUserQuery
{
    public function __construct(
        public string $userId,
    ) {
    }
}
