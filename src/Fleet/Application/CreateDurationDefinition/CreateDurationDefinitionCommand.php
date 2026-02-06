<?php

declare(strict_types=1);

namespace Fleet\Application\CreateDurationDefinition;

final readonly class CreateDurationDefinitionCommand
{
    public function __construct(
        public string $code,
        public string $label,
        public ?int $durationHours,
        public ?int $durationDays,
        public bool $isCustom,
        public int $sortOrder,
    ) {}
}
