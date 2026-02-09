<?php

declare(strict_types=1);

namespace Auth\Interface\Http\GetCurrentUser;

use Auth\Application\GetCurrentUser\GetCurrentUserHandler;
use Auth\Application\GetCurrentUser\GetCurrentUserQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller pour récupérer l'utilisateur courant.
 */
final class GetCurrentUserController
{
    public function __construct(
        private readonly GetCurrentUserHandler $handler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json([
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required',
                ],
            ], 401);
        }

        $query = new GetCurrentUserQuery(userId: $user->id());

        $response = $this->handler->handle($query);

        return response()->json($response->toArray());
    }
}
