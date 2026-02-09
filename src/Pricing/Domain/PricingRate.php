<?php

declare(strict_types=1);

namespace Pricing\Domain;

/**
 * Entité PricingRate du domaine - Représente un tarif pour une combinaison Category × Class × Duration.
 */
final class PricingRate
{
    private \DateTimeImmutable $createdAt;

    private \DateTimeImmutable $updatedAt;

    public function __construct(
        private readonly string $id,
        private readonly string $categoryId,
        private readonly string $pricingClassId,
        private readonly string $durationId,
        private float $price,
        private bool $isActive,
        private ?\DateTimeImmutable $deletedAt = null,
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null,
    ) {
        $this->createdAt = $createdAt ?? new \DateTimeImmutable;
        $this->updatedAt = $updatedAt ?? new \DateTimeImmutable;
        $this->validatePrice($price);
    }

    public static function create(
        string $id,
        string $categoryId,
        string $pricingClassId,
        string $durationId,
        float $price,
    ): self {
        return new self(
            id: $id,
            categoryId: $categoryId,
            pricingClassId: $pricingClassId,
            durationId: $durationId,
            price: $price,
            isActive: true,
        );
    }

    // ===== Getters =====

    public function id(): string
    {
        return $this->id;
    }

    public function categoryId(): string
    {
        return $this->categoryId;
    }

    public function pricingClassId(): string
    {
        return $this->pricingClassId;
    }

    public function durationId(): string
    {
        return $this->durationId;
    }

    public function price(): float
    {
        return $this->price;
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
     * Calcule le prix pour un nombre de jours donné (sans réductions).
     */
    public function calculatePriceForDays(int $days): float
    {
        if ($days < 1) {
            throw new \DomainException('Number of days must be at least 1');
        }

        return $this->price * $days;
    }

    // ===== Actions =====

    public function updatePrice(float $price): self
    {
        $this->validatePrice($price);

        $this->price = $price;
        $this->updatedAt = new \DateTimeImmutable;

        return $this;
    }

    public function activate(): self
    {
        if ($this->isActive) {
            throw new \DomainException('Pricing rate is already active');
        }

        $this->isActive = true;
        $this->updatedAt = new \DateTimeImmutable;

        return $this;
    }

    public function deactivate(): self
    {
        if (! $this->isActive) {
            throw new \DomainException('Pricing rate is already inactive');
        }

        $this->isActive = false;
        $this->updatedAt = new \DateTimeImmutable;

        return $this;
    }

    // ===== Validation =====

    private function validatePrice(float $price): void
    {
        if ($price <= 0) {
            throw new \DomainException('Price must be greater than 0');
        }
    }
}
