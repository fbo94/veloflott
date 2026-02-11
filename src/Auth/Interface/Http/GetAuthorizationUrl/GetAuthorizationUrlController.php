<?php

declare(strict_types=1);

namespace Auth\Interface\Http\GetAuthorizationUrl;

use Auth\Application\GetAuthorizationUrl\GetAuthorizationUrlHandler;
use Auth\Application\GetAuthorizationUrl\GetAuthorizationUrlQuery;
use Illuminate\Http\JsonResponse;

/**
 * Controller pour obtenir l'URL d'autorisation Keycloak avec PKCE.
 */
final class GetAuthorizationUrlController
{
    public function __construct(
        private readonly GetAuthorizationUrlHandler $handler,
    ) {
    }

    public function __invoke(GetAuthorizationUrlRequest $request): JsonResponse
    {
        $query = new GetAuthorizationUrlQuery(
            codeChallenge: $request->validated('code_challenge'),
            codeChallengeMethod: $request->validated('code_challenge_method', 'S256'),
            redirectUrl: $request->validated('redirect_url'),
        );

        $response = $this->handler->handle($query);

        return response()->json($response->toArray());
    }
}
