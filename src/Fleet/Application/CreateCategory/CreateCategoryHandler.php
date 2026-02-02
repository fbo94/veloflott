<?php

declare(strict_types=1);

namespace Fleet\Application\CreateCategory;

use Fleet\Domain\Category;
use Fleet\Domain\CategoryRepositoryInterface;
use Illuminate\Support\Str;

final class CreateCategoryHandler
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categories,
    ) {}

    public function handle(CreateCategoryCommand $command): string
    {
        // Vérifier que le nom n'existe pas déjà
        if ($this->categories->findByName($command->name) !== null) {
            throw new CategoryAlreadyExistsException($command->name);
        }

        $category = new Category(
            id: Str::uuid()->toString(),
            name: $command->name,
            description: $command->description,
            isDefault: false,
            displayOrder: 999, // Sera réordonné par l'utilisateur
        );

        $this->categories->save($category);

        return $category->id();
    }
}
