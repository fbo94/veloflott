<?php

declare(strict_types=1);

namespace Fleet\Domain;

enum RetirementReason: string
{
    case SOLD = 'sold';
    case STOLEN = 'stolen';
    case PERMANENTLY_OUT_OF_SERVICE = 'permanently_out_of_service';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::SOLD => 'Vendu',
            self::STOLEN => 'Volé',
            self::PERMANENTLY_OUT_OF_SERVICE => 'Hors service définitif',
            self::OTHER => 'Autre',
        };
    }
}
