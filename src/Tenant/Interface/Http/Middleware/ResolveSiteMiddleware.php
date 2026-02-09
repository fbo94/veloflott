<?php

declare(strict_types=1);

namespace Tenant\Interface\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tenant\Application\TenantContext;
use Tenant\Domain\SiteRepositoryInterface;

/**
 * Middleware qui résout optionnellement le site courant.
 *
 * Le site est résolu depuis:
 * 1. Le paramètre de route {site} ou {siteId}
 * 2. Le header X-Site-Id
 *
 * Ce middleware doit être exécuté APRÈS ResolveTenantMiddleware.
 * Le site est optionnel - certaines opérations peuvent être faites
 * au niveau tenant sans contexte de site spécifique.
 */
final class ResolveSiteMiddleware
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly SiteRepositoryInterface $siteRepository,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->tenantContext->hasTenant()) {
            return response()->json([
                'error' => 'Tenant context required',
                'message' => 'ResolveSiteMiddleware requires tenant to be resolved first',
            ], 500);
        }

        $siteId = $this->extractSiteId($request);

        // Le site est optionnel - on continue même s'il n'est pas spécifié
        if ($siteId === null) {
            return $next($request);
        }

        $site = $this->siteRepository->findById($siteId);

        if ($site === null) {
            return response()->json([
                'error' => 'Site not found',
                'message' => 'The specified site does not exist',
            ], 404);
        }

        // Vérifier que le site appartient au tenant courant
        if (!$site->belongsToTenant($this->tenantContext->requireTenantId())) {
            return response()->json([
                'error' => 'Site access denied',
                'message' => 'This site does not belong to your organization',
            ], 403);
        }

        if (!$site->canAcceptRentals()) {
            // On autorise l'accès mais on log un warning
            // Le site pourrait être suspendu ou fermé
        }

        $this->tenantContext->setSite($site);

        return $next($request);
    }

    private function extractSiteId(Request $request): ?string
    {
        // 1. Depuis le paramètre de route
        $siteId = $request->route('site') ?? $request->route('siteId');
        if ($siteId !== null) {
            return (string) $siteId;
        }

        // 2. Depuis le header X-Site-Id
        $headerSiteId = $request->header('X-Site-Id');
        if ($headerSiteId !== null) {
            return $headerSiteId;
        }

        return null;
    }
}
