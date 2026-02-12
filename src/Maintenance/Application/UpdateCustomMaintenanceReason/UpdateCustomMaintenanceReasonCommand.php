<?php

declare(strict_types=1);

namespace Maintenance\Application\UpdateCustomMaintenanceReason;

final readonly class UpdateCustomMaintenanceReasonCommand
{
    public function __construct(
        public string $id,
        public string $label,
        public ?string $description,
        public string $category,
        public bool $isActive,
        public int $sortOrder,
    ) {
    }
}
