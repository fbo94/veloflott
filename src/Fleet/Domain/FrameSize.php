<?php

declare(strict_types=1);

namespace Fleet\Domain;

/**
 * Value Object pour la taille de cadre.
 */
final readonly class FrameSize
{
    private function __construct(
        public FrameSizeUnit $unit,
        public ?float $numericValue,
        public ?FrameSizeLetter $letterValue,
        public FrameSizeLetter $letterEquivalent,
    ) {}

    public static function fromLetter(FrameSizeLetter $letter): self
    {
        return new self(
            unit: FrameSizeUnit::LETTER,
            numericValue: null,
            letterValue: $letter,
            letterEquivalent: $letter,
        );
    }

    public static function fromCentimeters(float $cm): self
    {
        return new self(
            unit: FrameSizeUnit::CM,
            numericValue: $cm,
            letterValue: null,
            letterEquivalent: FrameSizeLetter::fromNumeric($cm, FrameSizeUnit::CM),
        );
    }

    public static function fromInches(float $inches): self
    {
        return new self(
            unit: FrameSizeUnit::INCH,
            numericValue: $inches,
            letterValue: null,
            letterEquivalent: FrameSizeLetter::fromNumeric($inches, FrameSizeUnit::INCH),
        );
    }

    public static function fromRequest(?string $unit, ?float $numericValue, ?string $letterValue): self
    {
        if ($unit === FrameSizeUnit::LETTER->value && $letterValue !== null) {
            return self::fromLetter(FrameSizeLetter::from($letterValue));
        } elseif ($unit === FrameSizeUnit::CM->value && $numericValue !== null) {
            return self::fromCentimeters($numericValue);
        } elseif ($unit === FrameSizeUnit::INCH->value && $numericValue !== null) {
            return self::fromInches($numericValue);
        }

        throw new \InvalidArgumentException('Invalid frame size parameters');
    }

    public function displayValue(): string
    {
        return match ($this->unit) {
            FrameSizeUnit::LETTER => $this->letterValue->label(),
            FrameSizeUnit::CM => $this->numericValue.' cm',
            FrameSizeUnit::INCH => $this->numericValue.'"',
        };
    }

    public function toArray(): array
    {
        return [
            'unit' => $this->unit->value,
            'numeric_value' => $this->numericValue,
            'letter_value' => $this->letterValue?->value,
            'letter_equivalent' => $this->letterEquivalent->value,
            'display_value' => $this->displayValue(),
        ];
    }
}
