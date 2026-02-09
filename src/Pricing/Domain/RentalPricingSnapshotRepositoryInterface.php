<?php

declare(strict_types=1);

namespace Pricing\Domain;

interface RentalPricingSnapshotRepositoryInterface
{
    public function save(RentalPricingSnapshot $snapshot): void;

    public function findById(string $id): ?RentalPricingSnapshot;

    public function findByRentalId(string $rentalId): ?RentalPricingSnapshot;
}
