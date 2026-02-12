<?php

declare(strict_types=1);

namespace Maintenance\Application\DeleteCustomMaintenanceReason;

final readonly class DeleteCustomMaintenanceReasonCommand
{
    public function __construct(
        public string $id,
    ) {
    }
}
