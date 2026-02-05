<?php

declare(strict_types=1);

namespace Fleet\Domain;

/**
 * Entité Rate du domaine.
 * Un tarif peut être associé à une catégorie OU à un vélo spécifique.
 */
final class Rate
{
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        private readonly string $id,
        private ?string $categoryId,
        private ?string $bikeId,
        private ?PricingTier $pricingTier,
        private ?float $halfDayPrice,
        private float $dayPrice,
        private ?float $weekendPrice,
        private ?float $weekPrice,
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null,
    ) {
        if ($categoryId === null && $bikeId === null) {
            throw new \DomainException('A rate must be associated with either a category or a bike');
        }

        if ($categoryId !== null && $bikeId !== null) {
            throw new \DomainException('A rate cannot be associated with both a category and a bike');
        }

        // Si c'est un tarif par catégorie, il DOIT avoir un pricing_tier
        if ($categoryId !== null && $pricingTier === null) {
            throw new \DomainException('A category rate must have a pricing tier');
        }

        // Si c'est un tarif spécifique au vélo, il NE DOIT PAS avoir de pricing_tier
        if ($bikeId !== null && $pricingTier !== null) {
            throw new \DomainException('A bike-specific rate must not have a pricing tier');
        }

        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new \DateTimeImmutable();
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

    public function bikeId(): ?string
    {
        return $this->bikeId;
    }

    public function pricingTier(): ?PricingTier
    {
        return $this->pricingTier;
    }

    public function halfDayPrice(): ?float
    {
        return $this->halfDayPrice;
    }

    public function dayPrice(): float
    {
        return $this->dayPrice;
    }

    public function weekendPrice(): ?float
    {
        return $this->weekendPrice;
    }

    public function weekPrice(): ?float
    {
        return $this->weekPrice;
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

    public function updatePrices(
        ?float $halfDayPrice,
        float $dayPrice,
        ?float $weekendPrice,
        ?float $weekPrice,
    ): self {
        $this->halfDayPrice = $halfDayPrice;
        $this->dayPrice = $dayPrice;
        $this->weekendPrice = $weekendPrice;
        $this->weekPrice = $weekPrice;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getPriceFor(RateDuration $duration): ?float
    {
        return match ($duration) {
            RateDuration::HALF_DAY => $this->halfDayPrice,
            RateDuration::DAY => $this->dayPrice,
            RateDuration::WEEKEND => $this->weekendPrice,
            RateDuration::WEEK => $this->weekPrice,
        };
    }

    public function isCategoryRate(): bool
    {
        return $this->categoryId !== null;
    }

    public function isBikeSpecificRate(): bool
    {
        return $this->bikeId !== null;
    }
}
