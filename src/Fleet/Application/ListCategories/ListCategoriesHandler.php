<?php

declare(strict_types=1);

namespace Fleet\Application\ListCategories;

use Fleet\Domain\CategoryRepositoryInterface;

final class ListCategoriesHandler
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categories,
    ) {}

    public function handle(ListCategoriesQuery $query): ListCategoriesResponse
    {
        $categories = $this->categories->findAll();

        $categoryDtos = array_map(
            fn ($category) => CategoryDto::fromCategory($category),
            $categories
        );

        return new ListCategoriesResponse($categoryDtos);
    }
}
