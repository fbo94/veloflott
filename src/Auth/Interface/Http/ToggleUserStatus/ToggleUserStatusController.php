<?php

declare(strict_types=1);

namespace Auth\Interface\Http\ToggleUserStatus;

use Auth\Application\ToggleUserStatus\ToggleUserStatusCommand;
use Auth\Application\ToggleUserStatus\ToggleUserStatusHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller pour activer/désactiver un utilisateur.
 */
final class ToggleUserStatusController
{
    public function __construct(
        private readonly ToggleUserStatusHandler $handler,
    ) {}

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $currentUser = $request->user();

        if ($currentUser === null) {
            return response()->json([
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required',
                ],
            ], 401);
        }

        $command = new ToggleUserStatusCommand(
            userId: $id,
            currentUserId: $currentUser->id(),
        );

        $this->handler->handle($command);

        return response()->json([
            'message' => 'Statut modifié avec succès.',
        ]);
    }
}
