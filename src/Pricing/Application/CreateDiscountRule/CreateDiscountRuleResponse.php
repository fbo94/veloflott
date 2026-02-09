<?php

declare(strict_types=1);

namespace Pricing\Application\CreateDiscountRule;

use Pricing\Domain\DiscountRule;

final readonly class CreateDiscountRuleResponse
{
    private function __construct(
        public string $id,
        public ?string $categoryId,
        public ?string $pricingClassId,
        public ?int $minDays,
        public ?string $minDurationId,
        public string $discountType,
        public float $discountValue,
        public string $label,
        public ?string $description,
        public bool $isCumulative,
        public int $priority,
        public bool $isActive,
    ) {
    }

    public static function fromDomain(DiscountRule $discountRule): self
    {
        return new self(
            id: $discountRule->id(),
            categoryId: $discountRule->categoryId(),
            pricingClassId: $discountRule->pricingClassId(),
            minDays: $discountRule->minDays(),
            minDurationId: $discountRule->minDurationId(),
            discountType: $discountRule->discountType()->value,
            discountValue: $discountRule->discountValue(),
            label: $discountRule->label(),
            description: $discountRule->description(),
            isCumulative: $discountRule->isCumulative(),
            priority: $discountRule->priority(),
            isActive: $discountRule->isActive(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->categoryId,
            'pricing_class_id' => $this->pricingClassId,
            'min_days' => $this->minDays,
            'min_duration_id' => $this->minDurationId,
            'discount_type' => $this->discountType,
            'discount_value' => $this->discountValue,
            'label' => $this->label,
            'description' => $this->description,
            'is_cumulative' => $this->isCumulative,
            'priority' => $this->priority,
            'is_active' => $this->isActive,
        ];
    }
}
