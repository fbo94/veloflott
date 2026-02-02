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

    public function displayValue(): string
    {
        return match ($this->unit) {
            FrameSizeUnit::LETTER => $this->letterValue->label(),
            FrameSizeUnit::CM => $this->numericValue . ' cm',
            FrameSizeUnit::INCH => $this->numericValue . '"',
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
