<?php

declare(strict_types=1);

namespace Pricing\Application\CalculatePrice;

use Pricing\Domain\PriceCalculation;

final readonly class PriceCalculationDto
{
    /**
     * @param  array<string, mixed>  $appliedDiscounts
     */
    public function __construct(
        public float $basePrice,
        public float $finalPrice,
        public float $totalDiscountAmount,
        public float $totalDiscountPercentage,
        public int $days,
        public float $pricePerDay,
        public array $appliedDiscounts,
        public string $categoryId,
        public string $pricingClassId,
        public string $durationId,
    ) {}

    public static function fromDomain(PriceCalculation $calculation): self
    {
        return new self(
            basePrice: $calculation->basePrice,
            finalPrice: $calculation->finalPrice,
            totalDiscountAmount: $calculation->totalDiscountAmount(),
            totalDiscountPercentage: $calculation->totalDiscountPercentage(),
            days: $calculation->days,
            pricePerDay: $calculation->pricePerDay,
            appliedDiscounts: $calculation->toArray()['applied_discounts'],
            categoryId: $calculation->categoryId,
            pricingClassId: $calculation->pricingClassId,
            durationId: $calculation->durationId,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'base_price' => round($this->basePrice, 2),
            'final_price' => round($this->finalPrice, 2),
            'total_discount_amount' => round($this->totalDiscountAmount, 2),
            'total_discount_percentage' => round($this->totalDiscountPercentage, 2),
            'days' => $this->days,
            'price_per_day' => round($this->pricePerDay, 2),
            'applied_discounts' => $this->appliedDiscounts,
            'category_id' => $this->categoryId,
            'pricing_class_id' => $this->pricingClassId,
            'duration_id' => $this->durationId,
        ];
    }
}
