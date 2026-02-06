<?php

declare(strict_types=1);

namespace Fleet\Domain;

/**
 * Value Object représentant une plage de tailles (min-max).
 */
final readonly class SizeRange
{
    public function __construct(
        private int $min,
        private int $max,
    ) {
        if ($this->min < 0 || $this->max < 0) {
            throw new \DomainException('Size range values must be positive');
        }

        if ($this->min > $this->max) {
            throw new \DomainException('Minimum value cannot be greater than maximum value');
        }
    }

    public function min(): int
    {
        return $this->min;
    }

    public function max(): int
    {
        return $this->max;
    }

    public function contains(int|float $value): bool
    {
        return $value >= $this->min && $value <= $this->max;
    }

    /**
     * @return array{min: int, max: int}
     */
    public function toArray(): array
    {
        return [
            'min' => $this->min,
            'max' => $this->max,
        ];
    }
}
