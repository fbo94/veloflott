<?php

declare(strict_types=1);

namespace Fleet\Infrastructure\Persistence;

use Fleet\Domain\Category;
use Fleet\Domain\CategoryRepositoryInterface;
use Fleet\Infrastructure\Persistence\Models\CategoryEloquentModel;
use Fleet\Infrastructure\Persistence\Models\BikeEloquentModel;

final class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function findById(string $id): ?Category
    {
        $model = CategoryEloquentModel::find($id);

        return $model !== null ? $this->toDomain($model) : null;
    }

    public function findByName(string $name): ?Category
    {
        $model = CategoryEloquentModel::where('name', $name)->first();

        return $model !== null ? $this->toDomain($model) : null;
    }

    /**
     * @return Category[]
     */
    public function findAll(): array
    {
        return CategoryEloquentModel::all()
            ->map(fn ($model) => $this->toDomain($model))
            ->all();
    }

    /**
     * @return Category[]
     */
    public function findAllOrdered(): array
    {
        return CategoryEloquentModel::orderBy('display_order')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->all();
    }

    public function hasBikes(string $categoryId): bool
    {
        return BikeEloquentModel::where('category_id', $categoryId)->exists();
    }

    public function save(Category $category): void
    {
        CategoryEloquentModel::updateOrCreate(
            ['id' => $category->id()],
            [
                'name' => $category->name(),
                'description' => $category->description(),
                'is_default' => $category->isDefault(),
                'display_order' => $category->displayOrder(),
            ]
        );
    }

    public function delete(Category $category): void
    {
        CategoryEloquentModel::where('id', $category->id())->delete();
    }

    private function toDomain(CategoryEloquentModel $model): Category
    {
        return new Category(
            id: $model->id,
            name: $model->name,
            description: $model->description,
            isDefault: $model->is_default,
            displayOrder: $model->display_order,
            createdAt: new \DateTimeImmutable($model->created_at),
            updatedAt: new \DateTimeImmutable($model->updated_at),
        );
    }
}
