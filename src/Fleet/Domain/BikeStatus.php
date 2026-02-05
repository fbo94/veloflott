<?php

declare(strict_types=1);

namespace Fleet\Domain;

enum BikeStatus: string
{
    case AVAILABLE = 'available';
    case RENTED = 'rented';
    case MAINTENANCE = 'maintenance';
    case UNAVAILABLE = 'unavailable';
    case RETIRED = 'retired';

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Disponible',
            self::RENTED => 'En location',
            self::MAINTENANCE => 'En maintenance',
            self::UNAVAILABLE => 'Indisponible',
            self::RETIRED => 'Retiré',
        };
    }

    public function isRentable(): bool
    {
        return $this === self::AVAILABLE;
    }

    public function canBeModified(): bool
    {
        return $this !== self::RENTED;
    }
}
