<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\ListPricingRates;

use Fleet\Domain\PricingRateRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ListPricingRatesController
{
    public function __construct(
        private readonly PricingRateRepositoryInterface $repository,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $categoryId = $request->query('category_id');
        $pricingClassId = $request->query('pricing_class_id');

        $rates = $this->repository->findByCategoryAndClass($categoryId, $pricingClassId);

        return new JsonResponse([
            'data' => array_map(
                fn ($rate) => [
                    'id' => $rate->id(),
                    'category_id' => $rate->categoryId(),
                    'pricing_class_id' => $rate->pricingClassId(),
                    'duration_id' => $rate->durationId(),
                    'price' => $rate->price(),
                    'is_active' => $rate->isActive(),
                ],
                $rates
            ),
        ]);
    }
}
