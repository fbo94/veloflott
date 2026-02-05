<?php

declare(strict_types=1);

namespace Fleet\Application\ListModels;

final readonly class ListModelsQuery
{
    public function __construct(
        public ?string $brandId = null,
    ) {
    }
}
