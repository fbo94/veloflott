<?php

declare(strict_types=1);

namespace Auth\Application\GetAuthorizationUrl;

final readonly class GetAuthorizationUrlResponse
{
    public function __construct(
        public string $authorizationUrl,
        public string $state,
    ) {
    }

    public function toArray(): array
    {
        return [
            'authorization_url' => $this->authorizationUrl,
            'state' => $this->state,
        ];
    }
}
