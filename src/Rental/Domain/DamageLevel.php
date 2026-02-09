<?php

declare(strict_types=1);

namespace Rental\Domain;

enum DamageLevel: string
{
    case NONE = 'none';
    case MINOR = 'minor';
    case MAJOR = 'major';
    case TOTAL_LOSS = 'total_loss';

    public function label(): string
    {
        return match ($this) {
            self::NONE => 'Aucun dégât',
            self::MINOR => 'Dégâts mineurs',
            self::MAJOR => 'Dégâts majeurs',
            self::TOTAL_LOSS => 'Perte totale',
        };
    }

    public function requiresRetention(): bool
    {
        return $this !== self::NONE;
    }
}
