<?php

declare(strict_types=1);

namespace Pricing\Application\ListDefaultPricingClasses;

/**
 * Response contenant les classes tarifaires par défaut.
 */
final readonly class ListDefaultPricingClassesResponse
{
    /**
     * @param array<int, array{
     *     id: string,
     *     code: string,
     *     label: string,
     *     description: ?string,
     *     color: ?string,
     *     sort_order: int
     * }> $pricingClasses
     */
    public function __construct(
        public array $pricingClasses,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'data' => $this->pricingClasses,
        ];
    }
}
