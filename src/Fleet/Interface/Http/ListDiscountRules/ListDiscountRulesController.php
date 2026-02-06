<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\ListDiscountRules;

use Fleet\Domain\DiscountRuleRepositoryInterface;
use Illuminate\Http\JsonResponse;

final class ListDiscountRulesController
{
    public function __construct(
        private readonly DiscountRuleRepositoryInterface $repository,
    ) {}

    public function __invoke(): JsonResponse
    {
        $rules = $this->repository->findAllActive();

        return new JsonResponse([
            'data' => array_map(
                fn ($rule) => [
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
                ],
                $rules
            ),
        ]);
    }
}
