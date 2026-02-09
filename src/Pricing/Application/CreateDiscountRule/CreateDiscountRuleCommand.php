<?php

declare(strict_types=1);

namespace Pricing\Application\CreateDiscountRule;

final readonly class CreateDiscountRuleCommand
{
    public function __construct(
        public ?string $categoryId,
        public ?string $pricingClassId,
        public ?int $minDays,
        public ?string $minDurationId,
        public string $discountType,
        public float $discountValue,
        public string $label,
        public ?string $description = null,
        public bool $isCumulative = false,
        public int $priority = 0,
    ) {
    }
}
