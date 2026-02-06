<?php

declare(strict_types=1);

namespace Fleet\Domain;

/**
 * Value Object PriceCalculation - Représente le résultat d'un calcul de tarification.
 *
 * Cet objet est IMMUABLE et encapsule toutes les informations nécessaires
 * pour comprendre comment un prix a été calculé.
 */
final readonly class PriceCalculation
{
    /**
     * @param AppliedDiscount[] $appliedDiscounts
     */
    public function __construct(
        public float $basePrice,
        public float $finalPrice,
        public int $days,
        public float $pricePerDay,
        public array $appliedDiscounts,
        public string $categoryId,
        public string $pricingClassId,
        public string $durationId,
    ) {
        if ($basePrice < 0) {
            throw new \DomainException('Base price cannot be negative');
        }

        if ($finalPrice < 0) {
            throw new \DomainException('Final price cannot be negative');
        }

        if ($finalPrice > $basePrice) {
            throw new \DomainException('Final price cannot be greater than base price');
        }

        if ($days < 1) {
            throw new \DomainException('Days must be at least 1');
        }

        if ($pricePerDay < 0) {
            throw new \DomainException('Price per day cannot be negative');
        }
    }

    /**
     * Calcule le montant total des réductions.
     */
    public function totalDiscountAmount(): float
    {
        return $this->basePrice - $this->finalPrice;
    }

    /**
     * Calcule le pourcentage total de réduction.
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
        return !empty($this->appliedDiscounts);
    }

    /**
     * Retourne le nombre de réductions appliquées.
     */
    public function discountCount(): int
    {
        return count($this->appliedDiscounts);
    }

    /**
     * Convertit en tableau pour la serialization.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'base_price' => round($this->basePrice, 2),
            'final_price' => round($this->finalPrice, 2),
            'total_discount_amount' => round($this->totalDiscountAmount(), 2),
            'total_discount_percentage' => round($this->totalDiscountPercentage(), 2),
            'days' => $this->days,
            'price_per_day' => round($this->pricePerDay, 2),
            'applied_discounts' => array_map(
                fn (AppliedDiscount $discount) => $discount->toArray(),
                $this->appliedDiscounts
            ),
            'category_id' => $this->categoryId,
            'pricing_class_id' => $this->pricingClassId,
            'duration_id' => $this->durationId,
        ];
    }

    /**
     * Convertit en format pour RentalPricingSnapshot.
     *
     * @return array<string, mixed>
     */
    public function toSnapshotData(): array
    {
        return [
            'base_price' => $this->basePrice,
            'final_price' => $this->finalPrice,
            'discounts_applied' => array_map(
                fn (AppliedDiscount $discount) => $discount->toArray(),
                $this->appliedDiscounts
            ),
            'category_id' => $this->categoryId,
            'pricing_class_id' => $this->pricingClassId,
            'duration_id' => $this->durationId,
            'days' => $this->days,
            'price_per_day' => $this->pricePerDay,
        ];
    }
}
