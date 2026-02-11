<?php

declare(strict_types=1);

namespace Auth\Application\GetAuthorizationUrl;

use Auth\Infrastructure\Keycloak\KeycloakOAuthService;
use Illuminate\Support\Str;

final class GetAuthorizationUrlHandler
{
    public function __construct(
        private readonly KeycloakOAuthService $oauthService,
    ) {
    }

    public function handle(GetAuthorizationUrlQuery $query): GetAuthorizationUrlResponse
    {
        // Générer un state aléatoire pour la sécurité CSRF
        $state = Str::random(40);

        // Générer l'URL d'autorisation avec le PKCE fourni par le frontend
        $authorizationUrl = $this->oauthService->getAuthorizationUrl(
            $state,
            $query->codeChallenge,
            $query->codeChallengeMethod
        );

        return new GetAuthorizationUrlResponse(
            authorizationUrl: $authorizationUrl,
            state: $state,
        );
    }
}
