<?php

declare(strict_types=1);

namespace Pricing\Domain;

/**
 * Entité DiscountRule du domaine - Représente une règle de réduction dégressive.
 */
final class DiscountRule
{
    private \DateTimeImmutable $createdAt;

    private \DateTimeImmutable $updatedAt;

    public function __construct(
        private readonly string $id,
        private ?string $categoryId,
        private ?string $pricingClassId,
        private ?int $minDays,
        private ?string $minDurationId,
        private DiscountType $discountType,
        private float $discountValue,
        private string $label,
        private ?string $description,
        private bool $isCumulative,
        private int $priority,
        private bool $isActive,
        private ?\DateTimeImmutable $deletedAt = null,
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null,
    ) {
        $this->createdAt = $createdAt ?? new \DateTimeImmutable;
        $this->updatedAt = $updatedAt ?? new \DateTimeImmutable;
        $this->validateMinCondition($minDays, $minDurationId);
        $this->validateDiscountValue($discountType, $discountValue);
        $this->validateLabel($label);
    }

    public static function create(
        string $id,
        ?string $categoryId,
        ?string $pricingClassId,
        ?int $minDays,
        ?string $minDurationId,
        DiscountType $discountType,
        float $discountValue,
        string $label,
        ?string $description = null,
        bool $isCumulative = false,
        int $priority = 0,
    ): self {
        return new self(
            id: $id,
            categoryId: $categoryId,
            pricingClassId: $pricingClassId,
            minDays: $minDays,
            minDurationId: $minDurationId,
            discountType: $discountType,
            discountValue: $discountValue,
            label: $label,
            description: $description,
            isCumulative: $isCumulative,
            priority: $priority,
            isActive: true,
        );
    }

    // ===== Getters =====

    public function id(): string
    {
        return $this->id;
    }

    public function categoryId(): ?string
    {
        return $this->categoryId;
    }

    public function pricingClassId(): ?string
    {
        return $this->pricingClassId;
    }

    public function minDays(): ?int
    {
        return $this->minDays;
    }

    public function minDurationId(): ?string
    {
        return $this->minDurationId;
    }

    public function discountType(): DiscountType
    {
        return $this->discountType;
    }

    public function discountValue(): float
    {
        return $this->discountValue;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function isCumulative(): bool
    {
        return $this->isCumulative;
    }

    public function priority(): int
    {
        return $this->priority;
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
     * Vérifie si cette réduction s'applique à une catégorie donnée.
     */
    public function appliesToCategory(?string $categoryId): bool
    {
        return $this->categoryId === null || $this->categoryId === $categoryId;
    }

    /**
     * Vérifie si cette réduction s'applique à une classe de tarification donnée.
     */
    public function appliesToPricingClass(?string $pricingClassId): bool
    {
        return $this->pricingClassId === null || $this->pricingClassId === $pricingClassId;
    }

    /**
     * Vérifie si cette réduction s'applique pour un nombre de jours donné.
     */
    public function appliesToDays(int $days): bool
    {
        if ($this->minDays === null) {
            return true;
        }

        return $days >= $this->minDays;
    }

    /**
     * Calcule le montant de la réduction pour un prix donné.
     */
    public function calculateDiscount(float $basePrice): float
    {
        return match ($this->discountType) {
            DiscountType::PERCENTAGE => ($basePrice * $this->discountValue) / 100,
            DiscountType::FIXED => min($this->discountValue, $basePrice),
        };
    }

    // ===== Actions =====

    public function update(
        ?string $categoryId,
        ?string $pricingClassId,
        ?int $minDays,
        ?string $minDurationId,
        DiscountType $discountType,
        float $discountValue,
        string $label,
        ?string $description,
        bool $isCumulative,
        int $priority,
    ): self {
        $this->validateMinCondition($minDays, $minDurationId);
        $this->validateDiscountValue($discountType, $discountValue);
        $this->validateLabel($label);

        $this->categoryId = $categoryId;
        $this->pricingClassId = $pricingClassId;
        $this->minDays = $minDays;
        $this->minDurationId = $minDurationId;
        $this->discountType = $discountType;
        $this->discountValue = $discountValue;
        $this->label = $label;
        $this->description = $description;
        $this->isCumulative = $isCumulative;
        $this->priority = $priority;
        $this->updatedAt = new \DateTimeImmutable;

        return $this;
    }

    public function activate(): self
    {
        if ($this->isActive) {
            throw new \DomainException('Discount rule is already active');
        }

        $this->isActive = true;
        $this->updatedAt = new \DateTimeImmutable;

        return $this;
    }

    public function deactivate(): self
    {
        if (! $this->isActive) {
            throw new \DomainException('Discount rule is already inactive');
        }

        $this->isActive = false;
        $this->updatedAt = new \DateTimeImmutable;

        return $this;
    }

    // ===== Validation =====

    private function validateMinCondition(?int $minDays, ?string $minDurationId): void
    {
        if ($minDays === null && $minDurationId === null) {
            throw new \DomainException('Discount rule must have either minDays or minDurationId specified');
        }

        if ($minDays !== null && $minDays < 1) {
            throw new \DomainException('Minimum days must be at least 1');
        }
    }

    private function validateDiscountValue(DiscountType $discountType, float $discountValue): void
    {
        if ($discountValue <= 0) {
            throw new \DomainException('Discount value must be greater than 0');
        }

        if ($discountType === DiscountType::PERCENTAGE && $discountValue > 100) {
            throw new \DomainException('Percentage discount cannot exceed 100%');
        }
    }

    private function validateLabel(string $label): void
    {
        if (empty($label)) {
            throw new \DomainException('Discount label cannot be empty');
        }

        if (strlen($label) > 100) {
            throw new \DomainException('Discount label cannot exceed 100 characters');
        }
    }
}
