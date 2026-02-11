<?php

declare(strict_types=1);

namespace Tenant\Interface\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tenant\Application\TenantContext;
use Tenant\Domain\TenantRepositoryInterface;

/**
 * Middleware qui résout le tenant depuis le JWT Keycloak.
 *
 * Le tenant est résolu depuis:
 * 1. Le claim "organization" du JWT (Keycloak Organizations)
 * 2. Fallback: header X-Tenant-Id (pour dev/tests)
 *
 * Ce middleware doit être exécuté après l'authentification.
 *
 * Exception : Les Super Admins peuvent passer sans tenant context.
 */
final class ResolveTenantMiddleware
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly TenantRepositoryInterface $tenantRepository,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $this->extractTenantId($request);

        // Super Admin bypass : peut passer sans tenant context
        if ($tenantId === null) {
            $user = $request->user();
            if ($user !== null && $user->isSuperAdmin()) {
                // Le super admin continue sans tenant context
                return $next($request);
            }

            return response()->json([
                'error' => 'Tenant context required',
                'message' => 'Unable to determine tenant from request',
            ], 403);
        }

        $tenant = $this->tenantRepository->findById($tenantId);

        if ($tenant === null) {
            return response()->json([
                'error' => 'Tenant not found',
                'message' => 'The specified tenant does not exist',
            ], 404);
        }

        if (!$tenant->isActive()) {
            return response()->json([
                'error' => 'Tenant inactive',
                'message' => 'This tenant account is suspended or archived',
            ], 403);
        }

        $this->tenantContext->setTenant($tenant);

        return $next($request);
    }

    private function extractTenantId(Request $request): ?string
    {
        // 1. Depuis le JWT payload stocké par KeycloakAuthenticate
        $jwtPayload = $request->attributes->get('jwt_payload');
        if ($jwtPayload !== null) {
            $organizationId = $this->getOrganizationFromJwt($jwtPayload);
            if ($organizationId !== null) {
                return $organizationId;
            }
        }

        // 2. Fallback: Header X-Tenant-Id (pour dev/tests uniquement)
        $headerTenantId = $request->header('X-Tenant-Id');
        if ($headerTenantId !== null && $this->isDevOrTestEnvironment()) {
            return $headerTenantId;
        }

        return null;
    }

    /**
     * Extrait l'organization ID depuis le payload JWT.
     */
    private function getOrganizationFromJwt(object $payload): ?string
    {
        // Keycloak Organizations ajoute le claim "organization" avec l'ID de l'org
        if (isset($payload->organization)) {
            // Le claim peut être un objet avec l'ID ou directement l'ID
            if (is_object($payload->organization) && isset($payload->organization->id)) {
                return (string) $payload->organization->id;
            }

            if (is_string($payload->organization)) {
                return $payload->organization;
            }
        }

        // Alternative: chercher dans les claims personnalisés
        if (isset($payload->tenant_id)) {
            return (string) $payload->tenant_id;
        }

        return null;
    }

    private function isDevOrTestEnvironment(): bool
    {
        $env = app()->environment();

        return in_array($env, ['local', 'development', 'testing'], true);
    }
}
