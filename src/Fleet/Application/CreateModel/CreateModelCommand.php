<?php

declare(strict_types=1);

namespace Fleet\Application\CreateModel;

final readonly class CreateModelCommand
{
    public function __construct(
        public string $name,
        public string $brandId,
    ) {}
}
