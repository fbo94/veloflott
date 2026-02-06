<?php

declare(strict_types=1);

namespace Fleet\Application\CreateDuration;

use Fleet\Domain\DurationDefinition;

final readonly class CreateDurationResponse
{
    private function __construct(
        public string $id,
        public string $code,
        public string $label,
        public ?int $durationHours,
        public ?int $durationDays,
        public bool $isCustom,
        public int $sortOrder,
        public bool $isActive,
    ) {}

    public static function fromDomain(DurationDefinition $duration): self
    {
        return new self(
            id: $duration->id(),
            code: $duration->code(),
            label: $duration->label(),
            durationHours: $duration->durationHours(),
            durationDays: $duration->durationDays(),
            isCustom: $duration->isCustom(),
            sortOrder: $duration->sortOrder(),
            isActive: $duration->isActive(),
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
            'duration_hours' => $this->durationHours,
            'duration_days' => $this->durationDays,
            'is_custom' => $this->isCustom,
            'sort_order' => $this->sortOrder,
            'is_active' => $this->isActive,
        ];
    }
}
