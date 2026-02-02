<?php

declare(strict_types=1);

namespace Fleet\Domain;

enum WheelSize: string
{
    case TWENTY_SIX = '26';
    case TWENTY_SEVEN_FIVE = '27.5';
    case TWENTY_NINE = '29';
    case THIRTY_TWO = '32';
    case SIX_HUNDRED_FIFTY = '650b';
    case SEVEN_HUNDRED = '700c';

    public function label(): string
    {
        return match ($this) {
            self::TWENTY_SIX => '26"',
            self::TWENTY_SEVEN_FIVE => '27.5"',
            self::TWENTY_NINE => '29"',
            self::SEVEN_HUNDRED => '700c',
            self::SIX_HUNDRED_FIFTY => '650b',
            self::THIRTY_TWO => '32mm',
        };
    }
}
