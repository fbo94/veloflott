<?php

declare(strict_types=1);

namespace Fleet\Application\ListCategories;

final readonly class ListCategoriesResponse
{
    /**
     * @param  CategoryDto[]  $categories
     */
    public function __construct(
        public array $categories,
    ) {
    }

    public function toArray(): array
    {
        return [
            'data' => array_map(
                fn (CategoryDto $category) => $category->toArray(),
                $this->categories
            ),
        ];
    }
}
