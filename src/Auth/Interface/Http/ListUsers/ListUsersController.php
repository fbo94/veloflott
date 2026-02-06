<?php

declare(strict_types=1);

namespace Auth\Interface\Http\ListUsers;

use Auth\Application\ListUsers\ListUsersHandler;
use Auth\Application\ListUsers\ListUsersQuery;
use Auth\Domain\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller pour lister les utilisateurs.
 */
final class ListUsersController
{
    public function __construct(
        private readonly ListUsersHandler $handler,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $role = $request->query('role');
        $isActive = $request->query('is_active');

        $query = new ListUsersQuery(
            role: $role !== null ? Role::tryFrom($role) : null,
            isActive: $isActive !== null ? filter_var($isActive, FILTER_VALIDATE_BOOLEAN) : null,
        );

        $response = $this->handler->handle($query);

        return response()->json($response->toArray());
    }
}
