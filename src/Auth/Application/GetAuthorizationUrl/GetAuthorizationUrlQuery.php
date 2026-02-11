<?php

declare(strict_types=1);

namespace Auth\Application\GetAuthorizationUrl;

/**
 * Query pour obtenir l'URL d'autorisation Keycloak avec PKCE.
 */
final readonly class GetAuthorizationUrlQuery
{
    public function __construct(
        public string $codeChallenge,
        public string $codeChallengeMethod = 'S256',
        public ?string $redirectUrl = null,
    ) {
    }
}
