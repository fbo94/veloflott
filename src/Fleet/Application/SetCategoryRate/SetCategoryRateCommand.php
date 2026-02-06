<?php

declare(strict_types=1);

namespace Fleet\Application\SetCategoryRate;

final readonly class SetCategoryRateCommand
{
    public function __construct(
        public string $categoryId,
        public string $pricingTier,
        public float $dayPrice,
        public ?float $halfDayPrice = null,
        public ?float $weekendPrice = null,
        public ?float $weekPrice = null,
    ) {}
}
