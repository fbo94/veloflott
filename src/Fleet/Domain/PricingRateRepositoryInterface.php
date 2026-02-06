<?php

declare(strict_types=1);

namespace Fleet\Domain;

interface PricingRateRepositoryInterface
{
    public function save(PricingRate $rate): void;

    public function findById(string $id): ?PricingRate;

    public function findByDimensions(
        string $categoryId,
        string $pricingClassId,
        string $durationId
    ): ?PricingRate;

    /**
     * @return PricingRate[]
     */
    public function findByCategoryAndClass(string $categoryId, string $pricingClassId): array;

    /**
     * @return PricingRate[]
     */
    public function findByCategory(string $categoryId): array;

    /**
     * @return PricingRate[]
     */
    public function findAll(): array;

    public function delete(string $id): void;

    public function existsForDimensions(
        string $categoryId,
        string $pricingClassId,
        string $durationId,
        ?string $excludeId = null
    ): bool;
}
