<?php

declare(strict_types=1);

namespace Fleet\Application\UpdateSizeMappingConfiguration;

use Fleet\Domain\SizeMappingConfigurationRepositoryInterface;

final readonly class UpdateSizeMappingConfigurationHandler
{
    public function __construct(
        private SizeMappingConfigurationRepositoryInterface $repository,
    ) {
    }

    public function handle(UpdateSizeMappingConfigurationCommand $command): UpdateSizeMappingConfigurationResponse
    {
        $currentConfig = $this->repository->getActiveConfiguration();

        if ($currentConfig === null) {
            throw new \DomainException('No active configuration found to update');
        }

        $newConfig = $currentConfig->update(
            xsCm: $command->xsCm,
            xsInch: $command->xsInch,
            sCm: $command->sCm,
            sInch: $command->sInch,
            mCm: $command->mCm,
            mInch: $command->mInch,
            lCm: $command->lCm,
            lInch: $command->lInch,
            xlCm: $command->xlCm,
            xlInch: $command->xlInch,
            xxlCm: $command->xxlCm,
            xxlInch: $command->xxlInch,
        );

        // Save both configurations (old one is deactivated, new one is active)
        $this->repository->save($currentConfig);
        $this->repository->save($newConfig);

        return new UpdateSizeMappingConfigurationResponse($newConfig);
    }
}
