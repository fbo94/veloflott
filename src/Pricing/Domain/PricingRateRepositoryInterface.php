<?php

declare(strict_types=1);

namespace Pricing\Domain;

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
    public function findByCategoryAndClass(
        ?string $categoryId = null,
        ?string $pricingClassId = null
    ): array;

    /**
     * @return PricingRate[]
     */
    public function findAllActive(): array;

    /**
     * @return PricingRate[]
     */
    public function findAll(): array;

    /**
     * @return PricingRate[]
     */
    public function findByCategory(string $categoryId): array;

    public function delete(string $id): void;
}
