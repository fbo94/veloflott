<?php

declare(strict_types=1);

namespace Rental\Domain\Repository;

use Rental\Domain\DepositRetentionConfig;
use Rental\Domain\DepositRetentionConfigId;

interface DepositRetentionConfigRepositoryInterface
{
    public function save(DepositRetentionConfig $config): void;

    public function findById(DepositRetentionConfigId $id): ?DepositRetentionConfig;

    public function findByBikeId(string $bikeId): ?DepositRetentionConfig;

    public function findByPricingClassId(string $pricingClassId): ?DepositRetentionConfig;

    public function findByCategoryId(string $categoryId): ?DepositRetentionConfig;

    /**
     * Get all configs
     *
     * @return DepositRetentionConfig[]
     */
    public function findAll(): array;

    public function delete(DepositRetentionConfigId $id): void;
}
