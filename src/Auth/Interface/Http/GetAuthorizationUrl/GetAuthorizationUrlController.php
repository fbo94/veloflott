<?php

declare(strict_types=1);

namespace Auth\Interface\Http\GetAuthorizationUrl;

use Auth\Application\GetAuthorizationUrl\GetAuthorizationUrlHandler;
use Auth\Application\GetAuthorizationUrl\GetAuthorizationUrlQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller pour obtenir l'URL d'autorisation Keycloak.
 */
final class GetAuthorizationUrlController
{
    public function __construct(
        private readonly GetAuthorizationUrlHandler $handler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $query = new GetAuthorizationUrlQuery(
            redirectUrl: $request->query('redirect_url'),
        );

        $response = $this->handler->handle($query);

        return response()->json($response->toArray());
    }
}
