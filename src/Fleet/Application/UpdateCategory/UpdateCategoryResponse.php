<?php

declare(strict_types=1);

namespace Fleet\Application\UpdateCategory;

use Fleet\Domain\Category;

final readonly class UpdateCategoryResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
    ) {
    }

    public static function fromCategory(Category $category): self
    {
        return new self(
            id: $category->id(),
            name: $category->name(),
            description: $category->description(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
