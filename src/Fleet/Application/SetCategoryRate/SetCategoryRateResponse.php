<?php

declare(strict_types=1);

namespace Fleet\Application\SetCategoryRate;

final readonly class SetCategoryRateResponse
{
    public function __construct(
        public string $rateId,
        public string $categoryId,
        public string $pricingTier,
        public string $message,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->rateId,
            'category_id' => $this->categoryId,
            'pricing_tier' => $this->pricingTier,
            'message' => $this->message,
        ];
    }
}
