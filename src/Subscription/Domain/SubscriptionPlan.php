<?php

declare(strict_types=1);

namespace Subscription\Domain;

/**
 * Entité SubscriptionPlan (Plan d'abonnement).
 *
 * Représente un plan d'abonnement avec ses limites et tarifs.
 */
final class SubscriptionPlan
{
    private \DateTimeImmutable $createdAt;

    private \DateTimeImmutable $updatedAt;

    /**
     * @param array<string, mixed>|null $features
     */
    public function __construct(
        private readonly string $id,
        private string $name,
        private string $displayName,
        private ?string $description,
        private ?float $priceMonthly,
        private ?float $priceYearly,
        private int $maxUsers,
        private int $maxBikes,
        private int $maxSites,
        private ?array $features,
        private bool $isActive,
        private int $sortOrder,
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null,
    ) {
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new \DateTimeImmutable();
    }

    // ===== Getters =====

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function displayName(): string
    {
        return $this->displayName;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function priceMonthly(): ?float
    {
        return $this->priceMonthly;
    }

    public function priceYearly(): ?float
    {
        return $this->priceYearly;
    }

    public function maxUsers(): int
    {
        return $this->maxUsers;
    }

    public function maxBikes(): int
    {
        return $this->maxBikes;
    }

    public function maxSites(): int
    {
        return $this->maxSites;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function features(): ?array
    {
        return $this->features;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // ===== Actions =====

    public function activate(): self
    {
        $this->isActive = true;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function deactivate(): self
    {
        $this->isActive = false;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }
}
