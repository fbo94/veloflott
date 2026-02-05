<?php

declare(strict_types=1);

namespace Fleet\Domain;

interface BrandRepositoryInterface
{
    public function findById(string $id): ?Brand;

    public function findByName(string $name): ?Brand;

    /**
     * @return Brand[]
     */
    public function findAll(): array;

    public function save(Brand $brand): void;

    public function delete(string $id): void;
}
