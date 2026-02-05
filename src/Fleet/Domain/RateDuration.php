<?php

declare(strict_types=1);

namespace Fleet\Domain;

enum RateDuration: string
{
    case HALF_DAY = 'half_day';
    case DAY = 'day';
    case WEEKEND = 'weekend';
    case WEEK = 'week';

    public function label(): string
    {
        return match ($this) {
            self::HALF_DAY => 'Demi-journée',
            self::DAY => 'Journée',
            self::WEEKEND => 'Week-end',
            self::WEEK => 'Semaine',
        };
    }
}
