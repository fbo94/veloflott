<?php

declare(strict_types=1);

namespace Rental\Domain;

enum EarlyReturnFeeType: string
{
    case PERCENTAGE = 'percentage';
    case FIXED = 'fixed';
    case NONE = 'none';

    public function label(): string
    {
        return match ($this) {
            self::PERCENTAGE => 'Pourcentage',
            self::FIXED => 'Montant fixe',
            self::NONE => 'Aucun frais',
        };
    }
}
