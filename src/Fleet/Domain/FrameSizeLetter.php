<?php

declare(strict_types=1);

namespace Fleet\Domain;

enum FrameSizeLetter: string
{
    case XXS = 'xxs';
    case XS = 'xs';
    case S = 's';
    case M = 'm';
    case L = 'l';
    case XL = 'xl';
    case XXL = 'xxl';

    public function label(): string
    {
        return strtoupper($this->value);
    }

    /**
     * Correspondance par défaut en centimètres (min-max).
     *
     * @return array{min: int, max: int}
     */
    public function defaultCmRange(): array
    {
        return match ($this) {
            self::XXS => ['min' => 44, 'max' => 47],
            self::XS => ['min' => 48, 'max' => 50],
            self::S => ['min' => 51, 'max' => 53],
            self::M => ['min' => 54, 'max' => 56],
            self::L => ['min' => 57, 'max' => 59],
            self::XL => ['min' => 60, 'max' => 62],
            self::XXL => ['min' => 63, 'max' => 999],
        };
    }

    /**
     * Correspondance par défaut en pouces (min-max).
     *
     * @return array{min: int, max: int}
     */
    public function defaultInchRange(): array
    {
        return match ($this) {
            self::XXS => ['min' => 12, 'max' => 12],
            self::XS => ['min' => 13, 'max' => 14],
            self::S => ['min' => 15, 'max' => 16],
            self::M => ['min' => 17, 'max' => 18],
            self::L => ['min' => 19, 'max' => 20],
            self::XL => ['min' => 21, 'max' => 22],
            self::XXL => ['min' => 23, 'max' => 999],
        };
    }

    /**
     * Calcule la taille lettre à partir d'une valeur numérique.
     */
    public static function fromNumeric(float $value, FrameSizeUnit $unit): self
    {
        if ($unit === FrameSizeUnit::LETTER) {
            throw new \InvalidArgumentException('Cannot calculate letter size from letter unit');
        }

        foreach (self::cases() as $letter) {
            $range = $unit === FrameSizeUnit::CM
                ? $letter->defaultCmRange()
                : $letter->defaultInchRange();

            if ($value >= $range['min'] && $value <= $range['max']) {
                return $letter;
            }
        }

        // Si aucune correspondance, retourner XXL
        return self::XXL;
    }
}
