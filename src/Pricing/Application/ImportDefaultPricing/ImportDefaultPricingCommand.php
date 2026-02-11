<?php

declare(strict_types=1);

namespace Pricing\Application\ImportDefaultPricing;

/**
 * Command pour importer une grille tarifaire d'un tenant source vers un tenant cible.
 */
final readonly class ImportDefaultPricingCommand
{
    public function __construct(
        public string $targetTenantId,
        public ?string $sourceTenantId = null,
        public bool $copyPricingClasses = true,
        public bool $copyDurations = true,
        public bool $copyRates = true,
        public bool $copyDiscountRules = true,
    ) {
    }
}
