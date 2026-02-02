<?php

declare(strict_types=1);

namespace Fleet\Application\DeleteCategory;

use Fleet\Domain\BikeRepositoryInterface;
use Fleet\Domain\CategoryRepositoryInterface;

final class DeleteCategoryHandler
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categories,
        private readonly BikeRepositoryInterface $bikes,
    ) {}

    public function handle(DeleteCategoryCommand $command): void
    {
        // Vérifier que la catégorie existe
        $category = $this->categories->findById($command->id);
        if ($category === null) {
            throw new CategoryNotFoundException($command->id);
        }

        // Vérifier qu'aucun vélo n'utilise cette catégorie
        $bikesCount = $this->bikes->countByCategoryId($command->id);
        if ($bikesCount > 0) {
            throw new CategoryHasBikesException($command->id, $bikesCount);
        }

        // Supprimer
        $this->categories->delete($command->id);
    }
}
