<?php

declare(strict_types=1);

namespace Fleet\Domain;

enum BrakeType: string
{
    case HYDRAULIC_DISC = 'hydraulic_disc';
    case MECHANICAL_DISC = 'mechanical_disc';
    case MECHANICAL_RIM = 'mechanical_rim';
    case HYDRAULIC_RIM = 'hydraulic_rim';
    case DRUM = 'drum';
    case NONE = 'none';

    public function label(): string
    {
        return match ($this) {
            self::HYDRAULIC_DISC => 'Disque hydraulique',
            self::MECHANICAL_DISC => 'Disque mécanique',
            self::MECHANICAL_RIM => 'Jante mécanique',
            self::HYDRAULIC_RIM => 'Jante hydraulique',
            self::DRUM => 'Tambour',
        };
    }
}
