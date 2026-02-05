<?php

declare(strict_types=1);

namespace Fleet\Application\SetCategoryRate;

use Fleet\Domain\CategoryRepositoryInterface;
use Fleet\Domain\PricingTier;
use Fleet\Domain\Rate;
use Fleet\Domain\RateRepositoryInterface;
use Illuminate\Support\Str;

final class SetCategoryRateHandler
{
    public function __construct(
        private readonly RateRepositoryInterface $rates,
        private readonly CategoryRepositoryInterface $categories,
    ) {
    }

    public function handle(SetCategoryRateCommand $command): SetCategoryRateResponse
    {
        // Vérifier que la catégorie existe
        $category = $this->categories->findById($command->categoryId);
        if ($category === null) {
            throw new \DomainException("La catégorie '{$command->categoryId}' n'existe pas.");
        }

        // Convertir le string en enum PricingTier
        $tier = PricingTier::from($command->pricingTier);

        // Vérifier s'il existe déjà un tarif pour cette catégorie + tier
        $existingRate = $this->rates->findByCategoryIdAndTier($command->categoryId, $tier);

        if ($existingRate !== null) {
            // Mettre à jour le tarif existant
            $updatedRate = $existingRate->updatePrices(
                halfDayPrice: $command->halfDayPrice,
                dayPrice: $command->dayPrice,
                weekendPrice: $command->weekendPrice,
                weekPrice: $command->weekPrice,
            );
            $this->rates->save($updatedRate);

            return new SetCategoryRateResponse(
                rateId: $updatedRate->id(),
                categoryId: $command->categoryId,
                pricingTier: $tier->value,
                message: 'Tarif mis à jour avec succès',
            );
        }

        // Créer un nouveau tarif
        $rate = Rate::forCategory(
            id: Str::uuid()->toString(),
            categoryId: $command->categoryId,
            pricingTier: $tier,
            dayPrice: $command->dayPrice,
            halfDayPrice: $command->halfDayPrice,
            weekendPrice: $command->weekendPrice,
            weekPrice: $command->weekPrice,
        );

        $this->rates->save($rate);

        return new SetCategoryRateResponse(
            rateId: $rate->id(),
            categoryId: $command->categoryId,
            pricingTier: $tier->value,
            message: 'Tarif créé avec succès',
        );
    }
}
