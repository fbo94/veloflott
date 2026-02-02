<?php

declare(strict_types=1);

namespace Rental\Domain;

enum RentalDuration: string
{
    case HALF_DAY = 'half_day';     // 4 heures
    case FULL_DAY = 'full_day';     // 8 heures
    case TWO_DAYS = 'two_days';
    case THREE_DAYS = 'three_days';
    case WEEK = 'week';             // 7 jours
    case CUSTOM = 'custom';         // Durée personnalisée

    public function hours(): int
    {
        return match ($this) {
            self::HALF_DAY => 4,
            self::FULL_DAY => 8,
            self::TWO_DAYS => 48,
            self::THREE_DAYS => 72,
            self::WEEK => 168,
            self::CUSTOM => 0, // Géré manuellement
        };
    }

    public function days(): float
    {
        return $this->hours() / 24;
    }
}
