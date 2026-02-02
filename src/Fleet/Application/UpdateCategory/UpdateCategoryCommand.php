<?php

declare(strict_types=1);

namespace Fleet\Application\UpdateCategory;

final readonly class UpdateCategoryCommand
{
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
    ) {}
}
