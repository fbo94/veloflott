<?php

declare(strict_types=1);

namespace Tenant\Interface\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tenant\Application\TenantContext;

/**
 * Middleware qui vérifie qu'un contexte tenant est bien résolu.
 *
 * À utiliser sur les routes qui REQUIÈRENT un tenant.
 * Contrairement à ResolveTenantMiddleware qui résout le tenant,
 * ce middleware vérifie simplement qu'il est présent.
 */
final class RequireTenantContext
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->tenantContext->hasTenant()) {
            return response()->json([
                'error' => 'Tenant context required',
                'message' => 'This endpoint requires a tenant context',
            ], 403);
        }

        return $next($request);
    }
}
