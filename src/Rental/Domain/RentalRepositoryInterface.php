<?php

declare(strict_types=1);

namespace Rental\Domain;

interface RentalRepositoryInterface
{
    public function findById(string $id): ?Rental;

    /**
     * @return Rental[]
     */
    public function findActiveRentals(): array;

    /**
     * @return Rental[]
     */
    public function findByCustomerId(string $customerId): array;

    /**
     * @return Rental[]
     */
    public function findLateRentals(): array;

    public function save(Rental $rental): void;

    public function saveWithItems(Rental $rental): void;
}
