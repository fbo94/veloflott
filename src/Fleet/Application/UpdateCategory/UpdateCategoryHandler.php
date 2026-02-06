<?php

declare(strict_types=1);

namespace Fleet\Application\UpdateCategory;

use Fleet\Domain\CategoryRepositoryInterface;

final class UpdateCategoryHandler
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categories,
    ) {}

    public function handle(UpdateCategoryCommand $command): UpdateCategoryResponse
    {
        // Vérifier que la catégorie existe
        $category = $this->categories->findById($command->id);
        if ($category === null) {
            throw new CategoryNotFoundException($command->id);
        }

        // Vérifier l'unicité du nom si modifié
        $existingWithName = $this->categories->findByName($command->name);
        if ($existingWithName !== null && $existingWithName->id() !== $command->id) {
            throw new CategoryNameAlreadyExistsException($command->name);
        }

        // Mettre à jour
        $category->update($command->name, $command->slug, $command->description);

        $this->categories->save($category);

        return UpdateCategoryResponse::fromCategory($category);
    }
}
