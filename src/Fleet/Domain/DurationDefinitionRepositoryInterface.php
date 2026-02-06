<?php

declare(strict_types=1);

namespace Fleet\Domain;

interface DurationDefinitionRepositoryInterface
{
    public function save(DurationDefinition $duration): void;

    public function findById(string $id): ?DurationDefinition;

    public function findByCode(string $code): ?DurationDefinition;

    /**
     * @return DurationDefinition[]
     */
    public function findAllActive(): array;

    /**
     * @return DurationDefinition[]
     */
    public function findAll(): array;

    public function delete(string $id): void;

    public function existsWithCode(string $code, ?string $excludeId = null): bool;
}
