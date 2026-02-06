<?php

declare(strict_types=1);

namespace Fleet\Application\GetActiveSizeMappingConfiguration;

use Fleet\Domain\SizeMappingConfiguration;

final readonly class GetActiveSizeMappingConfigurationResponse
{
    public function __construct(
        private SizeMappingConfiguration $configuration,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->configuration->toArray();
    }
}
