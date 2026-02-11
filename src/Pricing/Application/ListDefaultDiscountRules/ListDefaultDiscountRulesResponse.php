<?php

declare(strict_types=1);

namespace Pricing\Application\ListDefaultDiscountRules;

/**
 * Response contenant les règles de réduction par défaut.
 */
final readonly class ListDefaultDiscountRulesResponse
{
    /**
     * @param array<int, array{id: string, category_id: ?string, pricing_class_id: ?string, min_days: ?int, min_duration_id: ?string, discount_type: string, discount_value: float, label: string, description: ?string, is_cumulative: bool, priority: int}> $discountRules
     */
    public function __construct(
        public array $discountRules,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'data' => $this->discountRules,
        ];
    }
}
