<?php

declare(strict_types=1);

namespace Fleet\Domain;

interface RentalPricingSnapshotRepositoryInterface
{
    public function save(RentalPricingSnapshot $snapshot): void;

    public function findById(string $id): ?RentalPricingSnapshot;

    public function findByRentalId(string $rentalId): ?RentalPricingSnapshot;

    /**
     * Note: Les snapshots sont immuables et ne peuvent pas être supprimés.
     * Ils sont supprimés automatiquement avec la location (cascade).
     */
}
