<?php

declare(strict_types=1);

namespace Fleet\Application\UpdateRate;

use Fleet\Domain\Rate;

final readonly class UpdateRateResponse
{
    public function __construct(
        public string $id,
        public ?string $categoryId,
        public ?string $bikeId,
        public string $duration,
        public float $price,
    ) {}

    public static function fromRate(Rate $rate): self
    {
        return new self(
            id: $rate->id(),
            categoryId: $rate->categoryId(),
            bikeId: $rate->bikeId(),
            duration: $rate->duration()->value,
            price: $rate->price(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->categoryId,
            'bike_id' => $this->bikeId,
            'duration' => $this->duration,
            'price' => $this->price,
        ];
    }
}
