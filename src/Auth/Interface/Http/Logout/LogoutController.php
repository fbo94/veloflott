<?php

declare(strict_types=1);

namespace Auth\Interface\Http\Logout;

use Auth\Application\Logout\LogoutCommand;
use Auth\Application\Logout\LogoutHandler;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Controller pour la déconnexion utilisateur.
 */
final class LogoutController
{
    public function __construct(
        private readonly LogoutHandler $handler,
    ) {
    }

    public function __invoke(LogoutRequest $request): JsonResponse
    {
        try {
            $command = new LogoutCommand(
                refreshToken: $request->validated('refresh_token'),
            );

            $this->handler->handle($command);

            return response()->json([
                'message' => 'Déconnexion réussie.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Échec de la déconnexion.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
