<?php

declare(strict_types=1);

namespace Maintenance\Domain;

enum MaintenancePriority: string
{
    case NORMAL = 'normal';
    case URGENT = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::NORMAL => 'Normale',
            self::URGENT => 'Urgente',
        };
    }

    public function isUrgent(): bool
    {
        return $this === self::URGENT;
    }
}
