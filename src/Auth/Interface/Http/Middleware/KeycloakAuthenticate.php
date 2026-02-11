<?php

declare(strict_types=1);

namespace Auth\Interface\Http\Middleware;

use Auth\Infrastructure\Keycloak\KeycloakTokenValidator;
use Auth\Infrastructure\Keycloak\UserSyncService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware d'authentification Keycloak.
 *
 * 1. Extrait le JWT du header Authorization
 * 2. Valide le token via Keycloak JWKS
 * 3. Synchronise l'utilisateur local
 * 4. Vérifie que l'utilisateur est actif
 * 5. Injecte l'utilisateur dans la request
 * 6. Stocke le payload JWT pour les middlewares suivants
 */
final class KeycloakAuthenticate
{
    public function __construct(
        private readonly KeycloakTokenValidator $tokenValidator,
        private readonly UserSyncService $userSyncService,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        // 1. Extraire le token
        $token = $this->extractToken($request);

        if ($token === null) {
            return $this->unauthorizedResponse('Token required');
        }

        // 2. Valider le token
        $payload = $this->tokenValidator->validate($token);

        if ($payload === null) {
            return $this->unauthorizedResponse('Invalid token');
        }

        // 3. Synchroniser l'utilisateur
        $user = $this->userSyncService->sync($payload);

        // 4. Vérifier que l'utilisateur est actif
        if (! $user->isActive()) {
            return $this->forbiddenResponse('User deactivated');
        }

        // 5. Injecter l'utilisateur dans la request
        $request->setUserResolver(fn () => $user);

        // 6. Stocker le payload JWT pour les middlewares suivants (ex: ResolveTenantMiddleware)
        $request->attributes->set('jwt_payload', $payload);

        return $next($request);
    }

    private function extractToken(Request $request): ?string
    {
        $header = $request->header('Authorization', '');

        if (! str_starts_with($header, 'Bearer ')) {
            return null;
        }

        $token = substr($header, 7);

        return $token !== '' ? $token : null;
    }

    private function unauthorizedResponse(string $message): Response
    {
        return response()->json([
            'error' => [
                'code' => 'UNAUTHORIZED',
                'message' => $message,
            ],
        ], 401);
    }

    private function forbiddenResponse(string $message): Response
    {
        return response()->json([
            'error' => [
                'code' => 'FORBIDDEN',
                'message' => $message,
            ],
        ], 403);
    }
}
