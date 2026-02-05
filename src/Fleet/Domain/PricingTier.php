<?php

declare(strict_types=1);

namespace Fleet\Domain;

enum PricingTier: string
{
    case STANDARD = 'standard';
    case PREMIUM = 'premium';

    public function label(): string
    {
        return match ($this) {
            self::STANDARD => 'Standard',
            self::PREMIUM => 'Premium',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::STANDARD => 'Tarif standard pour vélos classiques',
            self::PREMIUM => 'Tarif premium pour vélos haut de gamme',
        };
    }
}
