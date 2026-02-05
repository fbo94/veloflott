<?php

declare(strict_types=1);

namespace Rental\Domain;

enum EquipmentType: string
{
    case HELMET = 'helmet';
    case KNEE_PADS = 'knee_pads';
    case ELBOW_PADS = 'elbow_pads';
    case GLOVES = 'gloves';
    case BACKPACK = 'backpack';
    case LOCK = 'lock';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::HELMET => 'Casque',
            self::KNEE_PADS => 'Genouillères',
            self::ELBOW_PADS => 'Coudières',
            self::GLOVES => 'Gants',
            self::BACKPACK => 'Sac à dos',
            self::LOCK => 'Antivol',
            self::OTHER => 'Autre',
        };
    }

    public function defaultPrice(): float
    {
        return match ($this) {
            self::HELMET => 5.0,
            self::KNEE_PADS => 3.0,
            self::ELBOW_PADS => 3.0,
            self::GLOVES => 2.0,
            self::BACKPACK => 4.0,
            self::LOCK => 2.0,
            self::OTHER => 0.0,
        };
    }
}
