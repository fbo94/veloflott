<?php

declare(strict_types=1);

namespace Maintenance\Application\GetCustomMaintenanceReasonDetail;

final readonly class GetCustomMaintenanceReasonDetailQuery
{
    public function __construct(
        public string $id,
    ) {
    }
}
