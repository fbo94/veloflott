<?php

declare(strict_types=1);

namespace Pricing\Application\CreatePricingClass;

use Pricing\Domain\PricingClass;

final readonly class PricingClassDto
{
    public function __construct(
        public string $id,
        public string $code,
        public string $label,
        public ?string $description,
        public ?string $color,
        public int $sortOrder,
        public bool $isActive,
    ) {}

    public static function fromDomain(PricingClass $pricingClass): self
    {
        return new self(
            id: $pricingClass->id(),
            code: $pricingClass->code(),
            label: $pricingClass->label(),
            description: $pricingClass->description(),
            color: $pricingClass->color(),
            sortOrder: $pricingClass->sortOrder(),
            isActive: $pricingClass->isActive(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'label' => $this->label,
            'description' => $this->description,
            'color' => $this->color,
            'sort_order' => $this->sortOrder,
            'is_active' => $this->isActive,
        ];
    }
}
