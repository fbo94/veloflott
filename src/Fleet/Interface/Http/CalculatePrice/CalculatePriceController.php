<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\CalculatePrice;

use Fleet\Domain\Services\PricingCalculator;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class CalculatePriceController
{
    public function __construct(
        private readonly PricingCalculator $calculator,
    ) {
    }

    public function __invoke(CalculatePriceRequest $request): JsonResponse
    {
        $calculation = $this->calculator->calculate(
            categoryId: $request->input('category_id'),
            pricingClassId: $request->input('pricing_class_id'),
            durationId: $request->input('duration_id'),
            customDays: $request->input('custom_days'),
        );

        return new JsonResponse([
            'base_price' => $calculation->basePrice,
            'total_discount' => $calculation->totalDiscount,
            'final_price' => $calculation->finalPrice,
            'discounts' => array_map(
                fn ($discount) => [
                    'rule_id' => $discount['rule_id'],
                    'label' => $discount['label'],
                    'type' => $discount['type']->value,
                    'value' => $discount['value'],
                    'amount' => $discount['amount'],
                ],
                $calculation->discounts
            ),
        ], Response::HTTP_OK);
    }
}
