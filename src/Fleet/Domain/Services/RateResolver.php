<?php

declare(strict_types=1);

namespace Fleet\Domain\Services;

use Fleet\Domain\Bike;
use Fleet\Domain\CategoryRepositoryInterface;
use Fleet\Domain\Rate;
use Fleet\Domain\RateRepositoryInterface;

/**
 * Service de résolution de tarif pour un vélo.
 *
 * Hiérarchie de résolution :
 * 1. Tarif spécifique au vélo (bike_id)
 * 2. Tarif de la catégorie du vélo + tier du vélo
 * 3. Tarif de la catégorie parente + tier du vélo (si la catégorie a un parent)
 */
final class RateResolver
{
    public function __construct(
        private readonly RateRepositoryInterface $rateRepository,
        private readonly CategoryRepositoryInterface $categoryRepository,
    ) {
    }

    /**
     * Résout le tarif applicable pour un vélo donné.
     *
     * @throws RateNotFoundException Si aucun tarif n'est trouvé
     */
    public function resolveForBike(Bike $bike): Rate
    {
        // 1. Chercher un tarif spécifique au vélo
        $bikeRate = $this->rateRepository->findByBikeId($bike->id());
        if ($bikeRate !== null) {
            return $bikeRate;
        }

        // 2. Chercher un tarif pour la catégorie du vélo + son tier
        $categoryRate = $this->rateRepository->findByCategoryIdAndTier(
            $bike->categoryId(),
            $bike->pricingTier()
        );
        if ($categoryRate !== null) {
            return $categoryRate;
        }

        // 3. Si la catégorie a un parent, chercher un tarif pour la catégorie parente + tier
        $category = $this->categoryRepository->findById($bike->categoryId());
        if ($category !== null && $category->parentId() !== null) {
            $parentRate = $this->rateRepository->findByCategoryIdAndTier(
                $category->parentId(),
                $bike->pricingTier()
            );
            if ($parentRate !== null) {
                return $parentRate;
            }
        }

        // Aucun tarif trouvé
        throw new RateNotFoundException(
            "Aucun tarif trouvé pour le vélo {$bike->id()} "
            . "(catégorie: {$bike->categoryId()}, tier: {$bike->pricingTier()->value})"
        );
    }

    /**
     * Résout le tarif applicable pour un vélo et retourne null si non trouvé.
     */
    public function tryResolveForBike(Bike $bike): ?Rate
    {
        try {
            return $this->resolveForBike($bike);
        } catch (RateNotFoundException) {
            return null;
        }
    }
}
