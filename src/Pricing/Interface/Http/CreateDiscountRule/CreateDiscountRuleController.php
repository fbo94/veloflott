<?php

declare(strict_types=1);

namespace Pricing\Interface\Http\CreateDiscountRule;

use Illuminate\Http\JsonResponse;
use Pricing\Application\CreateDiscountRule\CreateDiscountRuleCommand;
use Pricing\Application\CreateDiscountRule\CreateDiscountRuleHandler;
use Symfony\Component\HttpFoundation\Response;

final readonly class CreateDiscountRuleController
{
    public function __construct(
        private CreateDiscountRuleHandler $handler,
    ) {
    }

    public function __invoke(CreateDiscountRuleRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $command = new CreateDiscountRuleCommand(
            categoryId: $validated['category_id'] ?? null,
            pricingClassId: $validated['pricing_class_id'] ?? null,
            minDays: $validated['min_days'] ?? null,
            minDurationId: $validated['min_duration_id'] ?? null,
            discountType: $validated['discount_type'],
            discountValue: $validated['discount_value'],
            label: $validated['label'],
            description: $validated['description'] ?? null,
            isCumulative: $validated['is_cumulative'] ?? false,
            priority: $validated['priority'] ?? 0,
        );

        $response = $this->handler->handle($command);

        return new JsonResponse($response->toArray(), Response::HTTP_CREATED);
    }
}
