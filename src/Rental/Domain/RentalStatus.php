<?php

declare(strict_types=1);

namespace Rental\Domain;

enum RentalStatus: string
{
    case PENDING = 'pending';       // Location créée mais pas encore démarrée
    case ACTIVE = 'active';         // Location en cours
    case COMPLETED = 'completed';   // Location terminée et check-out effectué
    case CANCELLED = 'cancelled';   // Location annulée

    public function canStart(): bool
    {
        return $this === self::PENDING;
    }

    public function canCheckOut(): bool
    {
        return $this === self::ACTIVE;
    }

    public function canCancel(): bool
    {
        return $this === self::PENDING;
    }
}
