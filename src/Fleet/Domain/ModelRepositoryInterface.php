<?php

declare(strict_types=1);

namespace Fleet\Domain;

interface ModelRepositoryInterface
{
    public function findById(string $id): ?Model;

    public function findByName(string $name): ?Model;

    /**
     * @return Model[]
     */
    public function findAll(): array;

    /**
     * @return Model[]
     */
    public function findByBrandId(string $brandId): array;

    public function countByBrandId(string $brandId): int;

    public function save(Model $model): void;

    public function delete(string $id): void;
}
