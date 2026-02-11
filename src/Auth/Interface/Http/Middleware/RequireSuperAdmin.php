<?php

declare(strict_types=1);

namespace Auth\Interface\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pour restreindre l'accès aux Super Admins uniquement.
 *
 * Usage : ->middleware('super-admin')
 */
final class RequireSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
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

        // Vérifier que l'utilisateur est Super Admin
        if (!$user->isSuperAdmin()) {
            return response()->json([
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Super Admin access required',
                ],
            ], 403);
        }

        return $next($request);
    }
}
