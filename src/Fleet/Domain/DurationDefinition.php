<?php

declare(strict_types=1);

namespace Fleet\Domain;

/**
 * Entité DurationDefinition du domaine - Représente une durée de location (Demi-journée, Journée, Week-end, etc.).
 */
final class DurationDefinition
{
    private \DateTimeImmutable $createdAt;

    private \DateTimeImmutable $updatedAt;

    public function __construct(
        private readonly string $id,
        private string $code,
        private string $label,
        private ?int $durationHours,
        private ?int $durationDays,
        private bool $isCustom,
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
        $this->validateDuration($durationHours, $durationDays);
    }

    public static function create(
        string $id,
        string $code,
        string $label,
        ?int $durationHours = null,
        ?int $durationDays = null,
        bool $isCustom = false,
        int $sortOrder = 0,
    ): self {
        return new self(
            id: $id,
            code: $code,
            label: $label,
            durationHours: $durationHours,
            durationDays: $durationDays,
            isCustom: $isCustom,
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

    public function durationHours(): ?int
    {
        return $this->durationHours;
    }

    public function durationDays(): ?int
    {
        return $this->durationDays;
    }

    public function isCustom(): bool
    {
        return $this->isCustom;
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

    /**
     * Calcule le nombre total d'heures pour cette durée.
     */
    public function totalHours(): ?int
    {
        if ($this->durationDays !== null) {
            return $this->durationDays * 24;
        }

        return $this->durationHours;
    }

    /**
     * Calcule le nombre approximatif de jours pour cette durée.
     */
    public function approximateDays(): ?float
    {
        if ($this->durationDays !== null) {
            return (float) $this->durationDays;
        }

        if ($this->durationHours !== null) {
            return $this->durationHours / 24.0;
        }

        return null;
    }

    // ===== Actions =====

    public function update(
        string $label,
        ?int $durationHours,
        ?int $durationDays,
        int $sortOrder,
    ): self {
        $this->validateLabel($label);
        $this->validateDuration($durationHours, $durationDays);

        $this->label = $label;
        $this->durationHours = $durationHours;
        $this->durationDays = $durationDays;
        $this->sortOrder = $sortOrder;
        $this->updatedAt = new \DateTimeImmutable;

        return $this;
    }

    public function activate(): self
    {
        if ($this->isActive) {
            throw new \DomainException('Duration is already active');
        }

        $this->isActive = true;
        $this->updatedAt = new \DateTimeImmutable;

        return $this;
    }

    public function deactivate(): self
    {
        if (! $this->isActive) {
            throw new \DomainException('Duration is already inactive');
        }

        $this->isActive = false;
        $this->updatedAt = new \DateTimeImmutable;

        return $this;
    }

    // ===== Validation =====

    private function validateCode(string $code): void
    {
        if (empty($code)) {
            throw new \DomainException('Duration code cannot be empty');
        }

        if (! preg_match('/^[a-z0-9_]+$/', $code)) {
            throw new \DomainException('Duration code must contain only lowercase letters, numbers and underscores');
        }

        if (strlen($code) > 50) {
            throw new \DomainException('Duration code cannot exceed 50 characters');
        }
    }

    private function validateLabel(string $label): void
    {
        if (empty($label)) {
            throw new \DomainException('Duration label cannot be empty');
        }

        if (strlen($label) > 100) {
            throw new \DomainException('Duration label cannot exceed 100 characters');
        }
    }

    private function validateDuration(?int $durationHours, ?int $durationDays): void
    {
        if ($durationHours === null && $durationDays === null && ! $this->isCustom) {
            throw new \DomainException('Duration must have either hours or days specified (or be marked as custom)');
        }

        if ($durationHours !== null && $durationHours < 1) {
            throw new \DomainException('Duration hours must be at least 1');
        }

        if ($durationDays !== null && $durationDays < 1) {
            throw new \DomainException('Duration days must be at least 1');
        }
    }
}
