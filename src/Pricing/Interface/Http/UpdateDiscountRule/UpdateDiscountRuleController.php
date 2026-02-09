<?php

declare(strict_types=1);

namespace Pricing\Interface\Http\UpdateDiscountRule;

use Illuminate\Http\JsonResponse;
use Pricing\Application\UpdateDiscountRule\UpdateDiscountRuleCommand;
use Pricing\Application\UpdateDiscountRule\UpdateDiscountRuleHandler;
use Symfony\Component\HttpFoundation\Response;

final readonly class UpdateDiscountRuleController
{
    public function __construct(
        private UpdateDiscountRuleHandler $handler,
    ) {
    }

    public function __invoke(string $id, UpdateDiscountRuleRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $command = new UpdateDiscountRuleCommand(
            id: $id,
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
            isActive: $validated['is_active'] ?? true,
        );

        try {
            $response = $this->handler->handle($command);

            return new JsonResponse($response->toArray(), Response::HTTP_OK);
        } catch (\DomainException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
