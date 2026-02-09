<?php

declare(strict_types=1);

namespace Auth\Interface\Http\Authorize;

use Auth\Application\Authorize\AuthorizeCommand;
use Auth\Application\Authorize\AuthorizeHandler;
use Illuminate\Http\JsonResponse;

/**
 * Controller pour le callback d'autorisation OAuth2.
 */
final class AuthorizeController
{
    public function __construct(
        private readonly AuthorizeHandler $handler,
    ) {
    }

    public function __invoke(AuthorizeRequest $request): JsonResponse
    {
        $command = new AuthorizeCommand(
            code: $request->validated('code'),
            state: $request->validated('state'),
        );

        $response = $this->handler->handle($command);

        return response()->json($response->toArray());
    }
}
