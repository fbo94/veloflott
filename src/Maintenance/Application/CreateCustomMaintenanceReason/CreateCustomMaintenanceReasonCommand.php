<?php

declare(strict_types=1);

namespace Maintenance\Application\CreateCustomMaintenanceReason;

final readonly class CreateCustomMaintenanceReasonCommand
{
    public function __construct(
        public string $code,
        public string $label,
        public ?string $description,
        public string $category,
        public bool $isActive = true,
        public int $sortOrder = 0,
    ) {
    }
}
