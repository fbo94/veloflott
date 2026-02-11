<?php

declare(strict_types=1);

namespace Pricing\Interface\Http\ImportDefaultPricing;

use Illuminate\Http\JsonResponse;
use Pricing\Application\ImportDefaultPricing\ImportDefaultPricingCommand;
use Pricing\Application\ImportDefaultPricing\ImportDefaultPricingHandler;
use Tenant\Application\TenantContext;

/**
 * Controller pour importer une grille tarifaire d'un tenant source vers un tenant cible.
 */
final class ImportDefaultPricingController
{
    public function __construct(
        private readonly ImportDefaultPricingHandler $handler,
        private readonly TenantContext $tenantContext,
    ) {
    }

    public function __invoke(ImportDefaultPricingRequest $request): JsonResponse
    {
        // target_tenant_id: si non fourni, utiliser le tenant courant
        $targetTenantId = $request->input('target_tenant_id')
            ?? $this->tenantContext->requireTenantId();

        $command = new ImportDefaultPricingCommand(
            targetTenantId: $targetTenantId,
            sourceTenantId: $request->input('source_tenant_id'),
            copyPricingClasses: (bool) $request->input('copy_pricing_classes', true),
            copyDurations: (bool) $request->input('copy_durations', true),
            copyRates: (bool) $request->input('copy_rates', true),
            copyDiscountRules: (bool) $request->input('copy_discount_rules', true),
        );

        $response = $this->handler->handle($command);

        return new JsonResponse(
            $response->toArray(),
            $response->success ? 200 : 400,
        );
    }
}
