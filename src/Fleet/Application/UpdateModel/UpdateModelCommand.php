<?php

declare(strict_types=1);

namespace Fleet\Application\UpdateModel;

final readonly class UpdateModelCommand
{
    public function __construct(
        public string $id,
        public string $name,
        public string $brandId,
    ) {}
}
