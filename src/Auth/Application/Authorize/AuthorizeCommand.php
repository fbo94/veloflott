<?php

declare(strict_types=1);

namespace Auth\Application\Authorize;

/**
 * Command pour échanger le code d'autorisation contre un token.
 */
final readonly class AuthorizeCommand
{
    public function __construct(
        public string $code,
        public string $state,
    ) {
    }
}
