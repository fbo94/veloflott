<?php

declare(strict_types=1);

namespace Pricing\Application\BulkUpdatePricingRates;

final readonly class BulkUpdatePricingRatesCommand
{
    /**
     * @param array<int, array{category_id: string, pricing_class_id: string, duration_id: string, price: float, is_active?: bool}> $rates
     */
    public function __construct(
        public array $rates,
    ) {
    }
}
