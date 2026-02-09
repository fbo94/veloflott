<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\SetPricingRate;

use Fleet\Domain\PricingRate;
use Fleet\Domain\PricingRateRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

final class SetPricingRateController
{
    public function __construct(
        private readonly PricingRateRepositoryInterface $repository,
    ) {
    }

    public function __invoke(SetPricingRateRequest $request): JsonResponse
    {
        // Check if rate already exists
        $existing = $this->repository->findByDimensions(
            $request->input('category_id'),
            $request->input('pricing_class_id'),
            $request->input('duration_id')
        );

        if ($existing !== null) {
            // Update existing rate
            $existing->updatePrice($request->input('price'));
            $this->repository->save($existing);
            $rate = $existing;
        } else {
            // Create new rate
            $rate = PricingRate::create(
                id: Uuid::uuid4()->toString(),
                categoryId: $request->input('category_id'),
                pricingClassId: $request->input('pricing_class_id'),
                durationId: $request->input('duration_id'),
                price: $request->input('price'),
            );
            $this->repository->save($rate);
        }

        return new JsonResponse([
            'id' => $rate->id(),
            'category_id' => $rate->categoryId(),
            'pricing_class_id' => $rate->pricingClassId(),
            'duration_id' => $rate->durationId(),
            'price' => $rate->price(),
            'is_active' => $rate->isActive(),
        ], Response::HTTP_OK);
    }
}
