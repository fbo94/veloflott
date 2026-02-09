<?php

declare(strict_types=1);

namespace Pricing\Domain;

/**
 * Value Object AppliedDiscount - Représente une réduction appliquée lors d'un calcul de prix.
 *
 * Cet objet est IMMUABLE.
 */
final readonly class AppliedDiscount
{
    public function __construct(
        public string $discountRuleId,
        public string $label,
        public DiscountType $type,
        public float $value,
        public float $amount,
    ) {
        if ($value < 0) {
            throw new \DomainException('Discount value cannot be negative');
        }

        if ($type === DiscountType::PERCENTAGE && $value > 100) {
            throw new \DomainException('Percentage discount cannot exceed 100%');
        }

        if ($amount < 0) {
            throw new \DomainException('Discount amount cannot be negative');
        }
    }

    /**
     * Convertit en tableau pour la serialization.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'discount_rule_id' => $this->discountRuleId,
            'label' => $this->label,
            'type' => $this->type->value,
            'value' => $this->value,
            'amount' => round($this->amount, 2),
        ];
    }

    /**
     * Formate la réduction pour l'affichage.
     */
    public function formatValue(): string
    {
        return match ($this->type) {
            DiscountType::PERCENTAGE => sprintf('-%s%%', number_format($this->value, 0)),
            DiscountType::FIXED => sprintf('-%s€', number_format($this->value, 2)),
        };
    }

    /**
     * Formate le montant pour l'affichage.
     */
    public function formatAmount(): string
    {
        return sprintf('-%s€', number_format($this->amount, 2));
    }
}
