<?php

declare(strict_types=1);

namespace Pricing\Application\CreateDuration;

final readonly class CreateDurationCommand
{
    public function __construct(
        public string $code,
        public string $label,
        public ?int $durationHours = null,
        public ?int $durationDays = null,
        public bool $isCustom = false,
        public int $sortOrder = 0,
    ) {
    }
}
