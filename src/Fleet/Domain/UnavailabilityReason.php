<?php

declare(strict_types=1);

namespace Fleet\Domain;

enum UnavailabilityReason: string
{
    case RESERVED = 'reserved';
    case LOANED = 'loaned';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::RESERVED => 'Réservé',
            self::LOANED => 'Prêt',
            self::OTHER => 'Autre',
        };
    }
}
