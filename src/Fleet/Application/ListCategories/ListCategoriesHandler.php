<?php

declare(strict_types=1);

namespace Fleet\Application\ListCategories;

use Fleet\Domain\Category;
use Fleet\Domain\CategoryRepositoryInterface;

final class ListCategoriesHandler
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categories,
    ) {}

    public function handle(ListCategoriesQuery $query): ListCategoriesResponse
    {
        $categories = $this->categories->findAllOrdered();

        // Organiser en structure hiérarchique
        $categoryDtos = $this->buildHierarchy($categories);

        return new ListCategoriesResponse($categoryDtos);
    }

    /**
     * @param array<Category> $categories
     * @return array<CategoryDto>
     */
    private function buildHierarchy(array $categories): array
    {
        // Indexer par ID pour accès rapide
        $categoriesById = [];
        foreach ($categories as $category) {
            $categoriesById[$category->id()] = $category;
        }

        // Construire la hiérarchie
        $rootCategories = [];
        $childrenByParentId = [];

        foreach ($categories as $category) {
            if ($category->isMainCategory()) {
                // Catégorie racine
                $rootCategories[] = $category;
            } else {
                // Catégorie enfant
                $parentId = $category->parentId();
                if (!isset($childrenByParentId[$parentId])) {
                    $childrenByParentId[$parentId] = [];
                }
                $childrenByParentId[$parentId][] = $category;
            }
        }

        // Convertir en DTOs avec enfants
        $result = [];
        foreach ($rootCategories as $rootCategory) {
            $children = [];
            if (isset($childrenByParentId[$rootCategory->id()])) {
                $children = array_map(
                    fn ($child) => CategoryDto::fromCategory($child),
                    $childrenByParentId[$rootCategory->id()]
                );
            }
            $result[] = CategoryDto::fromCategory($rootCategory, $children);
        }

        return $result;
    }
}
