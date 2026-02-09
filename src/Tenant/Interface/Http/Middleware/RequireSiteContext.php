<?php

declare(strict_types=1);

namespace Tenant\Interface\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tenant\Application\TenantContext;

/**
 * Middleware qui vérifie qu'un contexte site est bien résolu.
 *
 * À utiliser sur les routes qui REQUIÈRENT un site spécifique.
 * Par exemple, créer une location nécessite de savoir sur quel site.
 */
final class RequireSiteContext
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

        if (!$this->tenantContext->hasSite()) {
            return response()->json([
                'error' => 'Site context required',
                'message' => 'This endpoint requires a site context. Please specify X-Site-Id header.',
            ], 400);
        }

        if (!$this->tenantContext->requireSite()->canAcceptRentals()) {
            return response()->json([
                'error' => 'Site inactive',
                'message' => 'This site is not currently accepting operations',
            ], 403);
        }

        return $next($request);
    }
}
