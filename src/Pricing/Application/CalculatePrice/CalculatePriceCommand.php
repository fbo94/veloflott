<?php

declare(strict_types=1);

namespace Pricing\Application\CalculatePrice;

final readonly class CalculatePriceCommand
{
    public function __construct(
        public string $categoryId,
        public string $pricingClassId,
        public string $durationId,
        public ?int $customDays = null,
    ) {
    }
}
