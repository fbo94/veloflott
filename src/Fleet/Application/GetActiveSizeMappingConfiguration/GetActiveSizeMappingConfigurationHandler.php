<?php

declare(strict_types=1);

namespace Fleet\Application\GetActiveSizeMappingConfiguration;

use Fleet\Domain\SizeMappingConfigurationRepositoryInterface;

final readonly class GetActiveSizeMappingConfigurationHandler
{
    public function __construct(
        private SizeMappingConfigurationRepositoryInterface $repository,
    ) {
    }

    public function handle(GetActiveSizeMappingConfigurationQuery $query): GetActiveSizeMappingConfigurationResponse
    {
        $configuration = $this->repository->getActiveConfiguration();

        if ($configuration === null) {
            throw new \DomainException('No active size mapping configuration found');
        }

        return new GetActiveSizeMappingConfigurationResponse($configuration);
    }
}
