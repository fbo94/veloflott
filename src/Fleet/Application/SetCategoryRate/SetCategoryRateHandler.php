<?php

declare(strict_types=1);

namespace Fleet\Application\SetCategoryRate;

use Fleet\Domain\CategoryRepositoryInterface;
use Fleet\Domain\Rate;
use Fleet\Domain\RateRepositoryInterface;
use Illuminate\Support\Str;

final class SetCategoryRateHandler
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categories,
        private readonly RateRepositoryInterface $rates,
    ) {}

    public function handle(SetCategoryRateCommand $command): void
    {
        // Vérifier que la catégorie existe
        $category = $this->categories->findById($command->categoryId);
        if ($category === null) {
            throw new CategoryNotFoundException($command->categoryId);
        }

        // Récupérer ou créer le tarif
        $rate = $this->rates->findByCategoryId($command->categoryId);

        if ($rate === null) {
            $rate = new Rate(
                id: Str::uuid()->toString(),
                categoryId: $command->categoryId,
                bikeId: null,
                halfDayPrice: $command->halfDayPrice,
                dayPrice: $command->dayPrice,
                weekendPrice: $command->weekendPrice,
                weekPrice: $command->weekPrice,
            );
        } else {
            $rate->updatePrices(
                halfDayPrice: $command->halfDayPrice,
                dayPrice: $command->dayPrice,
                weekendPrice: $command->weekendPrice,
                weekPrice: $command->weekPrice,
            );
        }

        $this->rates->save($rate);
    }
}
