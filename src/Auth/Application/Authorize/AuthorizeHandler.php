<?php

declare(strict_types=1);

namespace Auth\Application\Authorize;

use Auth\Infrastructure\Keycloak\KeycloakOAuthService;
use Auth\Infrastructure\Keycloak\KeycloakTokenValidator;
use Auth\Infrastructure\Keycloak\UserSyncService;
use Exception;

final class AuthorizeHandler
{
    public function __construct(
        private readonly KeycloakOAuthService $oauthService,
        private readonly KeycloakTokenValidator $tokenValidator,
        private readonly UserSyncService $userSyncService,
    ) {}

    public function handle(AuthorizeCommand $command): AuthorizeResponse
    {
        try {
            // 1. Échanger le code contre un token
            $tokenData = $this->oauthService->exchangeCodeForToken($command->code);

            // 2. Valider le token reçu
            $payload = $this->tokenValidator->validate($tokenData['access_token']);

            if ($payload === null) {
                throw new InvalidAuthorizationCodeException('Token invalide reçu de Keycloak.');
            }

            // 3. Synchroniser l'utilisateur en base
            $user = $this->userSyncService->sync($payload);

            // 4. Vérifier que l'utilisateur est actif
            if (! $user->isActive()) {
                throw new UserDeactivatedException;
            }

            return new AuthorizeResponse(
                accessToken: $tokenData['access_token'],
                refreshToken: $tokenData['refresh_token'],
                expiresIn: $tokenData['expires_in'],
                tokenType: $tokenData['token_type'],
                user: [
                    'id' => $user->id(),
                    'email' => $user->email(),
                    'first_name' => $user->firstName(),
                    'last_name' => $user->lastName(),
                    'full_name' => $user->fullName(),
                    'role' => $user->role()->value,
                    'role_label' => $user->role()->label(),
                ],
            );
        } catch (Exception $e) {
            // Si c'est déjà une exception métier, on la propage
            if ($e instanceof InvalidAuthorizationCodeException || $e instanceof UserDeactivatedException) {
                throw $e;
            }

            // Sinon, on wrap dans une exception métier
            throw new InvalidAuthorizationCodeException(
                "Échec de l'échange du code d'autorisation : {$e->getMessage()}"
            );
        }
    }
}
