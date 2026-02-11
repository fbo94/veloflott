<?php

declare(strict_types=1);

namespace Auth\Application\Logout;

use Auth\Infrastructure\Keycloak\KeycloakOAuthService;
use Exception;

/**
 * Handler pour la déconnexion utilisateur.
 */
final readonly class LogoutHandler
{
    public function __construct(
        private KeycloakOAuthService $keycloakOAuthService,
    ) {
    }

    /**
     * @throws Exception
     */
    public function handle(LogoutCommand $command): void
    {
        $this->keycloakOAuthService->logout($command->refreshToken);
    }
}
