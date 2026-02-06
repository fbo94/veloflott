<?php

declare(strict_types=1);

namespace Fleet\Application\ResetSizeMappingConfiguration;

use Fleet\Domain\SizeMappingConfiguration;
use Fleet\Domain\SizeMappingConfigurationRepositoryInterface;
use Ramsey\Uuid\Uuid;

final readonly class ResetSizeMappingConfigurationHandler
{
    public function __construct(
        private SizeMappingConfigurationRepositoryInterface $repository,
    ) {}

    public function handle(ResetSizeMappingConfigurationCommand $command): ResetSizeMappingConfigurationResponse
    {
        $currentConfig = $this->repository->getActiveConfiguration();

        if ($currentConfig === null) {
            throw new \DomainException('No active configuration found');
        }

        // Deactivate current configuration
        $currentConfig->deactivate();
        $this->repository->save($currentConfig);

        // Create new default configuration with incremented version
        $newVersion = $currentConfig->version() + 1;
        $defaultConfig = SizeMappingConfiguration::createDefault(
            id: Uuid::uuid4()->toString(),
            version: $newVersion
        );

        $this->repository->save($defaultConfig);

        return new ResetSizeMappingConfigurationResponse($defaultConfig);
    }
}
