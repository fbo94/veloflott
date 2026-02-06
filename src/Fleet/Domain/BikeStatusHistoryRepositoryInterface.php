<?php

declare(strict_types=1);

namespace Fleet\Domain;

interface BikeStatusHistoryRepositoryInterface
{
    public function save(BikeStatusHistory $history): void;

    /**
     * @return BikeStatusHistory[]
     */
    public function findByBikeId(string $bikeId): array;
}
