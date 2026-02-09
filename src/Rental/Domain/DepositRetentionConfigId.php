<?php

declare(strict_types=1);

namespace Rental\Domain;

final readonly class DepositRetentionConfigId
{
    public function __construct(
        private string $value,
    ) {
    }

    public static function generate(): self
    {
        return new self(\Illuminate\Support\Str::uuid()->toString());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
