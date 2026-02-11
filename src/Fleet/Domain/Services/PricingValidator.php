<?php

declare(strict_types=1);

namespace Fleet\Domain\Services;

use Fleet\Domain\Bike;
use Fleet\Domain\DurationDefinition;
use Fleet\Domain\DurationDefinitionRepositoryInterface;
use Fleet\Domain\PricingRateRepositoryInterface;

/**
 * Service de domaine PricingValidator - Valide la disponibilité des tarifs pour un vélo.
 */
final readonly class PricingValidator
{
    public function __construct(
        private PricingRateRepositoryInterface $rateRepository,
        private DurationDefinitionRepositoryInterface $durationRepository,
    ) {
    }

    /**
     * Vérifie si un vélo peut être loué (a des tarifs configurés).
     */
    public function canBikeBeRented(Bike $bike): bool
    {
        // Le vélo doit avoir une classe de tarification
        $pricingClassId = $bike->pricingClass()?->id();
        if ($pricingClassId === null) {
            return false;
        }

        // Vérifie qu'au moins une durée active a un tarif
        $rates = $this->rateRepository->findByCategoryAndClass(
            $bike->categoryId(),
            $pricingClassId
        );

        foreach ($rates as $rate) {
            if ($rate->isActive()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retourne les durées disponibles pour un vélo.
     *
     * @return DurationDefinition[]
     */
    public function getAvailableDurations(Bike $bike): array
    {
        $pricingClassId = $bike->pricingClass()?->id();
        if ($pricingClassId === null) {
            return [];
        }

        // Récupérer toutes les durées actives
        $allDurations = $this->durationRepository->findAllActive();

        // Filtrer les durées qui ont un tarif pour ce vélo
        $availableDurations = [];
        foreach ($allDurations as $duration) {
            $rate = $this->rateRepository->findByDimensions(
                $bike->categoryId(),
                $pricingClassId,
                $duration->id()
            );

            if ($rate !== null && $rate->isActive()) {
                $availableDurations[] = $duration;
            }
        }

        // Trier par sort_order
        usort($availableDurations, function (DurationDefinition $a, DurationDefinition $b) {
            return $a->sortOrder() <=> $b->sortOrder();
        });

        return $availableDurations;
    }

    /**
     * Vérifie si une classe de tarification peut être supprimée.
     * (Ne peut pas être supprimée si des vélos l'utilisent)
     */
    public function canDeletePricingClass(string $pricingClassId, int $bikesCount): bool
    {
        return $bikesCount === 0;
    }

    /**
     * Vérifie si une durée peut être supprimée.
     * (Ne peut pas être supprimée si des tarifs l'utilisent)
     */
    public function canDeleteDuration(string $durationId): bool
    {
        // Chercher si des tarifs utilisent cette durée
        $allRates = $this->rateRepository->findAll();

        foreach ($allRates as $rate) {
            if ($rate->durationId() === $durationId && $rate->isActive()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Vérifie si une catégorie peut être supprimée du point de vue tarification.
     * (Ne peut pas être supprimée si des tarifs l'utilisent)
     */
    public function canDeleteCategory(string $categoryId): bool
    {
        $rates = $this->rateRepository->findByCategory($categoryId);

        foreach ($rates as $rate) {
            if ($rate->isActive()) {
                return false;
            }
        }

        return true;
    }
}
