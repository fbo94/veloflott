<?php

declare(strict_types=1);

namespace Pricing\Application\UpdateDuration;

final readonly class UpdateDurationCommand
{
    public function __construct(
        public string $id,
        public string $code,
        public string $label,
        public ?int $durationHours = null,
        public ?int $durationDays = null,
        public bool $isCustom = false,
        public int $sortOrder = 0,
        public bool $isActive = true,
    ) {
    }
}
