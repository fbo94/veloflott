<?php

declare(strict_types=1);

namespace Auth\Application\GetAuthorizationUrl;

use Auth\Infrastructure\Keycloak\KeycloakOAuthService;
use Illuminate\Support\Str;

final class GetAuthorizationUrlHandler
{
    public function __construct(
        private readonly KeycloakOAuthService $oauthService,
    ) {}

    public function handle(GetAuthorizationUrlQuery $query): GetAuthorizationUrlResponse
    {
        // Générer un state aléatoire pour la sécurité CSRF
        $state = Str::random(40);

        // Générer l'URL d'autorisation
        $authorizationUrl = $this->oauthService->getAuthorizationUrl($state);

        return new GetAuthorizationUrlResponse(
            authorizationUrl: $authorizationUrl,
            state: $state,
        );
    }
}
