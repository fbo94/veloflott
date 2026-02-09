<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\CreateDiscountRule;

use Fleet\Domain\DiscountRule;
use Fleet\Domain\DiscountRuleRepositoryInterface;
use Fleet\Domain\DiscountType;
use Illuminate\Http\JsonResponse;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

final class CreateDiscountRuleController
{
    public function __construct(
        private readonly DiscountRuleRepositoryInterface $repository,
    ) {
    }

    public function __invoke(CreateDiscountRuleRequest $request): JsonResponse
    {
        $rule = DiscountRule::create(
            id: Uuid::uuid4()->toString(),
            categoryId: $request->input('category_id'),
            pricingClassId: $request->input('pricing_class_id'),
            minDays: $request->input('min_days'),
            minDurationId: $request->input('min_duration_id'),
            discountType: DiscountType::from($request->input('discount_type')),
            discountValue: $request->input('discount_value'),
            label: $request->input('label'),
            description: $request->input('description'),
            isCumulative: $request->input('is_cumulative', false),
            priority: $request->input('priority', 0),
        );

        $this->repository->save($rule);

        return new JsonResponse([
            'id' => $rule->id(),
            'category_id' => $rule->categoryId(),
            'pricing_class_id' => $rule->pricingClassId(),
            'min_days' => $rule->minDays(),
            'min_duration_id' => $rule->minDurationId(),
            'discount_type' => $rule->discountType()->value,
            'discount_value' => $rule->discountValue(),
            'label' => $rule->label(),
            'description' => $rule->description(),
            'is_cumulative' => $rule->isCumulative(),
            'priority' => $rule->priority(),
            'is_active' => $rule->isActive(),
        ], Response::HTTP_CREATED);
    }
}
