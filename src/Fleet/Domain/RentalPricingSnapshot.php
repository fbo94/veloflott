<?php

declare(strict_types=1);

namespace Fleet\Domain;

/**
 * Entité RentalPricingSnapshot du domaine - Snapshot immuable du calcul de prix d'une location.
 *
 * Cette entité est IMMUABLE : une fois créée, elle ne peut plus être modifiée.
 * Elle sert à conserver l'historique exact du calcul de tarification au moment de la création de la location.
 */
final class RentalPricingSnapshot
{
    private \DateTimeImmutable $createdAt;

    private \DateTimeImmutable $updatedAt;

    /**
     * @param  array<string, mixed>  $discountsApplied
     */
    public function __construct(
        private readonly string $id,
        private readonly string $rentalId,
        private readonly float $basePrice,
        private readonly float $finalPrice,
        private readonly array $discountsApplied,
        private readonly string $categoryId,
        private readonly string $pricingClassId,
        private readonly string $durationId,
        private readonly int $days,
        private readonly float $pricePerDay,
        private readonly \DateTimeImmutable $calculatedAt,
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null,
    ) {
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new \DateTimeImmutable();
        $this->validatePrices($basePrice, $finalPrice);
        $this->validateDays($days);
    }

    /**
     * @param  array<string, mixed>  $discountsApplied
     */
    public static function create(
        string $id,
        string $rentalId,
        float $basePrice,
        float $finalPrice,
        array $discountsApplied,
        string $categoryId,
        string $pricingClassId,
        string $durationId,
        int $days,
        float $pricePerDay,
    ): self {
        return new self(
            id: $id,
            rentalId: $rentalId,
            basePrice: $basePrice,
            finalPrice: $finalPrice,
            discountsApplied: $discountsApplied,
            categoryId: $categoryId,
            pricingClassId: $pricingClassId,
            durationId: $durationId,
            days: $days,
            pricePerDay: $pricePerDay,
            calculatedAt: new \DateTimeImmutable(),
        );
    }

    // ===== Getters =====

    public function id(): string
    {
        return $this->id;
    }

    public function rentalId(): string
    {
        return $this->rentalId;
    }

    public function basePrice(): float
    {
        return $this->basePrice;
    }

    public function finalPrice(): float
    {
        return $this->finalPrice;
    }

    /**
     * @return array<string, mixed>
     */
    public function discountsApplied(): array
    {
        return $this->discountsApplied;
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

    public function days(): int
    {
        return $this->days;
    }

    public function pricePerDay(): float
    {
        return $this->pricePerDay;
    }

    public function calculatedAt(): \DateTimeImmutable
    {
        return $this->calculatedAt;
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

    /**
     * Calcule le montant total des réductions appliquées.
     */
    public function totalDiscountAmount(): float
    {
        return $this->basePrice - $this->finalPrice;
    }

    /**
     * Calcule le pourcentage de réduction total.
     */
    public function totalDiscountPercentage(): float
    {
        if ($this->basePrice === 0.0) {
            return 0.0;
        }

        return ($this->totalDiscountAmount() / $this->basePrice) * 100;
    }

    /**
     * Vérifie si des réductions ont été appliquées.
     */
    public function hasDiscounts(): bool
    {
        return !empty($this->discountsApplied);
    }

    /**
     * Retourne le nombre de réductions appliquées.
     */
    public function discountCount(): int
    {
        return count($this->discountsApplied);
    }

    /**
     * Convertit le snapshot en tableau pour serialization.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'rental_id' => $this->rentalId,
            'base_price' => $this->basePrice,
            'final_price' => $this->finalPrice,
            'total_discount_amount' => $this->totalDiscountAmount(),
            'total_discount_percentage' => round($this->totalDiscountPercentage(), 2),
            'discounts_applied' => $this->discountsApplied,
            'category_id' => $this->categoryId,
            'pricing_class_id' => $this->pricingClassId,
            'duration_id' => $this->durationId,
            'days' => $this->days,
            'price_per_day' => $this->pricePerDay,
            'calculated_at' => $this->calculatedAt->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }

    // ===== Validation =====

    private function validatePrices(float $basePrice, float $finalPrice): void
    {
        if ($basePrice < 0) {
            throw new \DomainException('Base price cannot be negative');
        }

        if ($finalPrice < 0) {
            throw new \DomainException('Final price cannot be negative');
        }

        if ($finalPrice > $basePrice) {
            throw new \DomainException('Final price cannot be greater than base price');
        }
    }

    private function validateDays(int $days): void
    {
        if ($days < 1) {
            throw new \DomainException('Number of days must be at least 1');
        }
    }
}
