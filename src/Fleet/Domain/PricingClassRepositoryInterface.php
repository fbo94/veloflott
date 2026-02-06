<?php

declare(strict_types=1);

namespace Fleet\Domain;

interface PricingClassRepositoryInterface
{
    public function save(PricingClass $pricingClass): void;

    public function findById(string $id): ?PricingClass;

    public function findByCode(string $code): ?PricingClass;

    /**
     * @return PricingClass[]
     */
    public function findAllActive(): array;

    /**
     * @return PricingClass[]
     */
    public function findAll(): array;

    public function delete(string $id): void;

    public function existsWithCode(string $code, ?string $excludeId = null): bool;
}
