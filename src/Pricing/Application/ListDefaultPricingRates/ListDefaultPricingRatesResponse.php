<?php

declare(strict_types=1);

namespace Pricing\Application\ListDefaultPricingRates;

/**
 * Response contenant les tarifs par défaut.
 */
final readonly class ListDefaultPricingRatesResponse
{
    /**
     * @param array<int, array{id: string, category_id: string, pricing_class_id: string, duration_id: string, price: float, is_active: bool}> $rates
     */
    public function __construct(
        public array $rates,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'data' => $this->rates,
        ];
    }
}
