<?php

declare(strict_types=1);

namespace Tenant\Domain;

enum TenantStatus: string
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Actif',
            self::SUSPENDED => 'Suspendu',
            self::ARCHIVED => 'Archivé',
        };
    }
}
