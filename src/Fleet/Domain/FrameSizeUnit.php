<?php

declare(strict_types=1);

namespace Fleet\Domain;

enum FrameSizeUnit: string
{
    case LETTER = 'letter';
    case CM = 'cm';
    case INCH = 'inch';

    public function label(): string
    {
        return match ($this) {
            self::LETTER => 'Lettres (XXS, XS, S, M, L, XL, XXL)',
            self::CM => 'Centimètres',
            self::INCH => 'Pouces',
        };
    }
}
