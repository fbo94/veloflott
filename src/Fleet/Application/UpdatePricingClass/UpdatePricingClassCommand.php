<?php

declare(strict_types=1);

namespace Fleet\Application\UpdatePricingClass;

final readonly class UpdatePricingClassCommand
{
    public function __construct(
        public string $id,
        public string $code,
        public string $label,
        public ?string $description = null,
        public ?string $color = null,
        public int $sortOrder = 0,
        public bool $isActive = true,
    ) {
    }
}
