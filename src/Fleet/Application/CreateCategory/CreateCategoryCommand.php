<?php

declare(strict_types=1);

namespace Fleet\Application\CreateCategory;

final readonly class CreateCategoryCommand
{
    public function __construct(
        public string $name,
        public ?string $slug,
        public ?string $description,
        public ?string $parentId = null,
    ) {}
}
