<?php

declare(strict_types=1);

namespace Fleet\Domain\Services;

use Fleet\Domain\AppliedDiscount;
use Fleet\Domain\DiscountRuleRepositoryInterface;
use Fleet\Domain\DurationDefinitionRepositoryInterface;
use Fleet\Domain\PriceCalculation;
use Fleet\Domain\PricingRateRepositoryInterface;

/**
 * Service de domaine PricingCalculator - Calcule le prix d'une location.
 */
final readonly class PricingCalculator
{
    public function __construct(
        private PricingRateRepositoryInterface $rateRepository,
        private DurationDefinitionRepositoryInterface $durationRepository,
        private DiscountRuleRepositoryInterface $discountRepository,
    ) {
    }

    /**
     * Calcule le prix d'une location.
     *
     * @throws NoPricingFoundException Si aucun tarif n'est trouvé
     */
    public function calculate(
        string $categoryId,
        string $pricingClassId,
        string $durationId,
        ?int $customDays = null,
    ): PriceCalculation {
        // 1. Récupérer la durée
        $duration = $this->durationRepository->findById($durationId);
        if ($duration === null) {
            throw new \DomainException("Duration not found: {$durationId}");
        }

        // 2. Calculer le nombre de jours
        $days = $customDays ?? $duration->durationDays() ?? 1;
        if ($days < 1) {
            throw new \DomainException('Number of days must be at least 1');
        }

        // 3. Résoudre le tarif de base
        $rate = $this->rateRepository->findByDimensions(
            $categoryId,
            $pricingClassId,
            $durationId
        );

        if ($rate === null || !$rate->isActive()) {
            throw new NoPricingFoundException(
                "No active pricing rate found for category={$categoryId}, class={$pricingClassId}, duration={$durationId}"
            );
        }

        // 4. Prix de base = tarif × jours
        $basePrice = $rate->price() * $days;

        // 5. Appliquer les réductions dégressives
        $discountRules = $this->discountRepository->findApplicableRules(
            $categoryId,
            $pricingClassId,
            $days
        );

        $finalPrice = $basePrice;
        $appliedDiscounts = [];

        foreach ($discountRules as $rule) {
            // Vérifier si la règle s'applique
            if (!$rule->isActive()) {
                continue;
            }

            if (!$rule->appliesToCategory($categoryId)) {
                continue;
            }

            if (!$rule->appliesToPricingClass($pricingClassId)) {
                continue;
            }

            if (!$rule->appliesToDays($days)) {
                continue;
            }

            // Calculer le montant de la réduction
            $discountAmount = $rule->calculateDiscount($finalPrice);

            // Appliquer la réduction
            $finalPrice -= $discountAmount;

            // Enregistrer la réduction appliquée
            $appliedDiscounts[] = new AppliedDiscount(
                discountRuleId: $rule->id(),
                label: $rule->label(),
                type: $rule->discountType(),
                value: $rule->discountValue(),
                amount: $discountAmount,
            );

            // Si la règle n'est pas cumulative, on arrête
            if (!$rule->isCumulative()) {
                break;
            }
        }

        // 6. S'assurer que le prix final n'est pas négatif
        $finalPrice = max($finalPrice, 0.0);

        // 7. Retourner le calcul détaillé
        return new PriceCalculation(
            basePrice: $basePrice,
            finalPrice: $finalPrice,
            days: $days,
            pricePerDay: $rate->price(),
            appliedDiscounts: $appliedDiscounts,
            categoryId: $categoryId,
            pricingClassId: $pricingClassId,
            durationId: $durationId,
        );
    }

    /**
     * Calcule un aperçu rapide du prix sans appliquer les réductions.
     */
    public function calculateQuickEstimate(
        string $categoryId,
        string $pricingClassId,
        string $durationId,
        int $days = 1,
    ): float {
        $rate = $this->rateRepository->findByDimensions(
            $categoryId,
            $pricingClassId,
            $durationId
        );

        if ($rate === null || !$rate->isActive()) {
            throw new NoPricingFoundException(
                'No active pricing rate found'
            );
        }

        return $rate->price() * $days;
    }
}
