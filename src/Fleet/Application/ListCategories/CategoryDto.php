<?php

declare(strict_types=1);

namespace Fleet\Application\ListCategories;

use Fleet\Domain\Category;

final readonly class CategoryDto
{
    /**
     * @param  array<CategoryDto>  $children
     */
    public function __construct(
        public string $id,
        public string $name,
        public ?string $slug,
        public ?string $description,
        public ?string $parentId,
        public array $children = [],
    ) {}

    public static function fromCategory(Category $category, array $children = []): self
    {
        return new self(
            id: $category->id(),
            name: $category->name(),
            slug: $category->slug(),
            description: $category->description(),
            parentId: $category->parentId(),
            children: $children,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'parent_id' => $this->parentId,
            'children' => array_map(fn (CategoryDto $child) => $child->toArray(), $this->children),
        ];
    }
}
