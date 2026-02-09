<?php

declare(strict_types=1);

namespace Auth\Application\Authorize;

final readonly class AuthorizeResponse
{
    /**
     * @param  array<string, mixed>  $user
     */
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
        public int $expiresIn,
        public string $tokenType,
        public array $user,
    ) {
    }

    public function toArray(): array
    {
        return [
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'expires_in' => $this->expiresIn,
            'token_type' => $this->tokenType,
            'user' => $this->user,
        ];
    }
}
