<?php

declare(strict_types=1);

namespace Pricing\Interface\Http\ListPricingClasses;

use Illuminate\Http\JsonResponse;
use Pricing\Domain\PricingClassRepositoryInterface;

final class ListPricingClassesController
{
    public function __construct(
        private readonly PricingClassRepositoryInterface $repository,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $pricingClasses = $this->repository->findAllActive();

        return new JsonResponse([
            'data' => array_map(
                fn ($pricingClass) => [
                    'id' => $pricingClass->id(),
                    'code' => $pricingClass->code(),
                    'label' => $pricingClass->label(),
                    'description' => $pricingClass->description(),
                    'color' => $pricingClass->color(),
                    'sort_order' => $pricingClass->sortOrder(),
                    'is_active' => $pricingClass->isActive(),
                ],
                $pricingClasses
            ),
        ]);
    }
}
