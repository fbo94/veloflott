<?php

declare(strict_types=1);

namespace Pricing\Application\ListDefaultPricingRates;

/**
 * Query pour récupérer les tarifs par défaut (template).
 */
final readonly class ListDefaultPricingRatesQuery
{
    public function __construct(
        public ?string $categoryId = null,
        public ?string $pricingClassId = null,
    ) {
    }
}
