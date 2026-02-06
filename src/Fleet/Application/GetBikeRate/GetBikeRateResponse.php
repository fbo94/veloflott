<?php

declare(strict_types=1);

namespace Fleet\Application\GetBikeRate;

use Fleet\Domain\Bike;
use Fleet\Domain\Rate;

final readonly class GetBikeRateResponse
{
    public function __construct(
        public string $rateId,
        public string $bikeId,
        public ?string $categoryId,
        public ?string $pricingTier,
        public string $rateType,
        public ?float $halfDayPrice,
        public float $dayPrice,
        public ?float $weekendPrice,
        public ?float $weekPrice,
    ) {}

    public static function fromRate(Rate $rate, Bike $bike): self
    {
        return new self(
            rateId: $rate->id(),
            bikeId: $bike->id(),
            categoryId: $rate->categoryId(),
            pricingTier: $rate->pricingTier()?->value,
            rateType: $rate->isBikeRate() ? 'bike_specific' : 'category',
            halfDayPrice: $rate->halfDayPrice(),
            dayPrice: $rate->dayPrice(),
            weekendPrice: $rate->weekendPrice(),
            weekPrice: $rate->weekPrice(),
        );
    }

    public function toArray(): array
    {
        return [
            'rate_id' => $this->rateId,
            'bike_id' => $this->bikeId,
            'category_id' => $this->categoryId,
            'pricing_tier' => $this->pricingTier,
            'rate_type' => $this->rateType,
            'prices' => [
                'half_day' => $this->halfDayPrice,
                'day' => $this->dayPrice,
                'weekend' => $this->weekendPrice,
                'week' => $this->weekPrice,
            ],
        ];
    }
}
