<?php

declare(strict_types=1);

namespace Auth\Interface\Http\Middleware;

use Auth\Domain\Permission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de vérification des permissions.
 *
 * Usage : ->middleware('permission:view_bikes')
 */
final class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        // Vérifier que l'utilisateur est authentifié
        if ($user === null) {
            return response()->json([
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required',
                ],
            ], 401);
        }

        // Convertir la string en enum Permission
        $permissionEnum = Permission::tryFrom($permission);

        if ($permissionEnum === null) {
            // Permission inconnue = erreur de config
            return response()->json([
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Invalid permission configuration',
                ],
            ], 500);
        }

        // Vérifier la permission
        if (! $user->hasPermission($permissionEnum)) {
            return response()->json([
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Permission denied',
                    'context' => [
                        'required_permission' => $permission,
                    ],
                ],
            ], 403);
        }

        return $next($request);
    }
}
