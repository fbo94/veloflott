<?php

declare(strict_types=1);

namespace Tenant\Domain;

enum SiteStatus: string
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Actif',
            self::SUSPENDED => 'Suspendu',
            self::CLOSED => 'Fermé',
        };
    }

    public function canAcceptRentals(): bool
    {
        return $this === self::ACTIVE;
    }

    public function canAcceptMaintenances(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isOperational(): bool
    {
        return $this === self::ACTIVE;
    }
}
