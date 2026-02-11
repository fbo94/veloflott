<?php

declare(strict_types=1);

namespace Auth\Application\Logout;

/**
 * Commande pour déconnecter un utilisateur.
 */
final readonly class LogoutCommand
{
    public function __construct(
        public string $refreshToken,
    ) {
    }
}
