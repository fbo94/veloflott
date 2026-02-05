<?php

declare(strict_types=1);

namespace Auth\Application\GetAuthorizationUrl;

/**
 * Query pour obtenir l'URL d'autorisation Keycloak.
 */
final readonly class GetAuthorizationUrlQuery
{
    public function __construct(
        public ?string $redirectUrl = null,
    ) {
    }
}
