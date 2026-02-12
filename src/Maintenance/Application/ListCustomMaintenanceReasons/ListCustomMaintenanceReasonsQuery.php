<?php

declare(strict_types=1);

namespace Maintenance\Application\ListCustomMaintenanceReasons;

final readonly class ListCustomMaintenanceReasonsQuery
{
    public function __construct(
        public ?string $category = null,
        public ?bool $isActive = null,
    ) {
    }
}
