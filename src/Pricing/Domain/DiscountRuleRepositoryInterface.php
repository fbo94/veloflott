<?php

declare(strict_types=1);

namespace Pricing\Domain;

interface DiscountRuleRepositoryInterface
{
    public function save(DiscountRule $rule): void;

    public function findById(string $id): ?DiscountRule;

    /**
     * @return DiscountRule[]
     */
    public function findAllActive(): array;

    /**
     * @return DiscountRule[]
     */
    public function findApplicableRules(
        string $categoryId,
        string $pricingClassId,
        int $days
    ): array;

    public function delete(string $id): void;
}
