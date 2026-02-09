<?php

declare(strict_types=1);

namespace Pricing\Domain;

/**
 * Entité PricingClass du domaine - Représente une classe de tarification (Standard, Premium, Luxe, etc.).
 */
final class PricingClass
{
    private \DateTimeImmutable $createdAt;

    private \DateTimeImmutable $updatedAt;

    public function __construct(
        private readonly string $id,
        private string $code,
        private string $label,
        private ?string $description,
        private ?string $color,
        private int $sortOrder,
        private bool $isActive,
        private ?\DateTimeImmutable $deletedAt = null,
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null,
    ) {
        $this->createdAt = $createdAt ?? new \DateTimeImmutable;
        $this->updatedAt = $updatedAt ?? new \DateTimeImmutable;
        $this->validateCode($code);
        $this->validateLabel($label);
        if ($color !== null) {
            $this->validateColor($color);
        }
    }

    public static function create(
        string $id,
        string $code,
        string $label,
        ?string $description = null,
        ?string $color = null,
        int $sortOrder = 0,
    ): self {
        return new self(
            id: $id,
            code: $code,
            label: $label,
            description: $description,
            color: $color,
            sortOrder: $sortOrder,
            isActive: true,
        );
    }

    // ===== Getters =====

    public function id(): string
    {
        return $this->id;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function color(): ?string
    {
        return $this->color;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function deletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // ===== Business Logic =====

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    // ===== Actions =====

    public function update(
        string $label,
        ?string $description,
        ?string $color,
        int $sortOrder,
    ): self {
        $this->validateLabel($label);
        if ($color !== null) {
            $this->validateColor($color);
        }

        $this->label = $label;
        $this->description = $description;
        $this->color = $color;
        $this->sortOrder = $sortOrder;
        $this->updatedAt = new \DateTimeImmutable;

        return $this;
    }

    public function activate(): self
    {
        if ($this->isActive) {
            throw new \DomainException('Pricing class is already active');
        }

        $this->isActive = true;
        $this->updatedAt = new \DateTimeImmutable;

        return $this;
    }

    public function deactivate(): self
    {
        if (! $this->isActive) {
            throw new \DomainException('Pricing class is already inactive');
        }

        $this->isActive = false;
        $this->updatedAt = new \DateTimeImmutable;

        return $this;
    }

    // ===== Validation =====

    private function validateCode(string $code): void
    {
        if (empty($code)) {
            throw new \DomainException('Pricing class code cannot be empty');
        }

        if (! preg_match('/^[a-z0-9_]+$/', $code)) {
            throw new \DomainException('Pricing class code must contain only lowercase letters, numbers and underscores');
        }

        if (strlen($code) > 50) {
            throw new \DomainException('Pricing class code cannot exceed 50 characters');
        }
    }

    private function validateLabel(string $label): void
    {
        if (empty($label)) {
            throw new \DomainException('Pricing class label cannot be empty');
        }

        if (strlen($label) > 100) {
            throw new \DomainException('Pricing class label cannot exceed 100 characters');
        }
    }

    private function validateColor(?string $color): void
    {
        if ($color !== null && ! preg_match('/^#[0-9A-F]{6}$/i', $color)) {
            throw new \DomainException('Pricing class color must be a valid hex color code (e.g., #3B82F6)');
        }
    }
}
