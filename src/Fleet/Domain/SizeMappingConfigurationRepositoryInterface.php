<?php

declare(strict_types=1);

namespace Fleet\Domain;

interface SizeMappingConfigurationRepositoryInterface
{
    public function getActiveConfiguration(): ?SizeMappingConfiguration;

    public function save(SizeMappingConfiguration $configuration): SizeMappingConfiguration;

    public function findById(string $id): ?SizeMappingConfiguration;

    public function getNextVersion(): int;
}
