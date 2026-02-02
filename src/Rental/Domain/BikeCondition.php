<?php

declare(strict_types=1);

namespace Rental\Domain;

enum BikeCondition: string
{
    case OK = 'ok';
    case MINOR_DAMAGE = 'minor_damage';
    case MAJOR_DAMAGE = 'major_damage';

    public function requiresMaintenance(): bool
    {
        return $this !== self::OK;
    }

    public function label(): string
    {
        return match ($this) {
            self::OK => 'OK',
            self::MINOR_DAMAGE => 'Dégâts mineurs',
            self::MAJOR_DAMAGE => 'Dégâts majeurs',
        };
    }
}
