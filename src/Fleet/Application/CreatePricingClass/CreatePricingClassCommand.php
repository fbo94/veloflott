<?php

declare(strict_types=1);

namespace Fleet\Application\CreatePricingClass;

final readonly class CreatePricingClassCommand
{
    public function __construct(
        public string $code,
        public string $label,
        public ?string $description,
        public ?string $color,
        public int $sortOrder,
    ) {
    }
}
