<?php

declare(strict_types=1);

namespace Fleet\Application\UpdateSizeMappingConfiguration;

use Fleet\Domain\SizeMappingConfiguration;

final readonly class UpdateSizeMappingConfigurationResponse
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
