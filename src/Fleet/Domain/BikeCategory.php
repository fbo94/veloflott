<?php

declare(strict_types=1);

namespace Fleet\Domain;

enum BikeCategory: string
{
    case ENDURO = 'enduro';
    case DH = 'dh';
    case XC = 'xc';
    case ALL_MOUNTAIN = 'all_mountain';
    case ROAD = 'road';
    case TT = 'tt';
    case TRIATHLON = 'triathlon';
    case CX = 'cx';
    case TRACK = 'track';
    case GRAVEL = 'gravel';
    case EMTB = 'emtb';
    case EBIKE = 'ebike';
    case MISC = 'misc';

    public function label(): string
    {
        return match ($this) {
            self::ENDURO => 'Enduro',
            self::DH => 'DH',
            self::XC => 'XC',
            self::ALL_MOUNTAIN => 'All Mountain',
            self::TT => 'Contre-la-montre',
            self::TRIATHLON => 'Triathlon',
            self::CX => 'Cyclo-cross',
            self::TRACK => 'Piste',
            self::ROAD => 'Route',
            self::GRAVEL => 'Gravel',
            self::EMTB => 'VTTAE',
            self::EBIKE => 'Vélo électrique',
            self::MISC => 'Divers',
        };
    }

    public function isDefault(): bool
    {
        return true; // Toutes les catégories enum sont par défaut
    }
}
