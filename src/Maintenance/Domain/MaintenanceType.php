<?php

declare(strict_types=1);

namespace Maintenance\Domain;

enum MaintenanceType: string
{
    case PREVENTIVE = 'preventive';
    case CURATIVE = 'curative';

    public function label(): string
    {
        return match ($this) {
            self::PREVENTIVE => 'Préventive',
            self::CURATIVE => 'Curative',
        };
    }
}
