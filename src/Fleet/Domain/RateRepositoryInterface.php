<?php

declare(strict_types=1);

namespace Fleet\Domain;

interface RateRepositoryInterface
{
    public function findById(string $id): ?Rate;

    public function findByCategoryIdAndTier(string $categoryId, PricingTier $tier): ?Rate;

    public function findByBikeId(string $bikeId): ?Rate;

    /**
     * @return Rate[]
     */
    public function findAllCategoryRates(): array;

    /**
     * @return Rate[]
     */
    public function findByCategoryId(string $categoryId): array;

    public function save(Rate $rate): void;

    public function delete(Rate $rate): void;
}
