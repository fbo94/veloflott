<?php

declare(strict_types=1);

namespace Auth\Interface\Http\UpdateUserRole;

use Auth\Application\UpdateUserRole\UpdateUserRoleCommand;
use Auth\Application\UpdateUserRole\UpdateUserRoleHandler;
use Auth\Domain\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller pour changer le rôle d'un utilisateur.
 */
final class UpdateUserRoleController
{
    public function __construct(
        private readonly UpdateUserRoleHandler $handler,
    ) {}

    public function __invoke(UpdateUserRoleRequest $request, string $id): JsonResponse
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

        $command = new UpdateUserRoleCommand(
            userId: $id,
            newRole: Role::from($request->validated('role')),
            currentUserId: $currentUser->id(),
        );

        $this->handler->handle($command);

        return response()->json([
            'message' => 'Rôle modifié avec succès.',
        ]);
    }
}
