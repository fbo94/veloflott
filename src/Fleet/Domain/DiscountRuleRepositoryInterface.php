<?php

declare(strict_types=1);

namespace Fleet\Domain;

interface DiscountRuleRepositoryInterface
{
    public function save(DiscountRule $rule): void;

    public function findById(string $id): ?DiscountRule;

    /**
     * Trouve toutes les règles de réduction applicables pour une catégorie, classe et nombre de jours donnés.
     *
     * @return DiscountRule[]
     */
    public function findApplicableRules(
        ?string $categoryId,
        ?string $pricingClassId,
        int $days
    ): array;

    /**
     * @return DiscountRule[]
     */
    public function findAllActive(): array;

    /**
     * @return DiscountRule[]
     */
    public function findAll(): array;

    public function delete(string $id): void;
}
