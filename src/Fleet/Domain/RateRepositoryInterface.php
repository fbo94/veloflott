<?php

declare(strict_types=1);

namespace Fleet\Domain;

interface RateRepositoryInterface
{
    public function findById(string $id): ?Rate;

    public function findByCategoryId(string $categoryId): ?Rate;

    public function findByBikeId(string $bikeId): ?Rate;

    /**
     * @return Rate[]
     */
    public function findAllCategoryRates(): array;

    public function save(Rate $rate): void;

    public function delete(Rate $rate): void;
}
