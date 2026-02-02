<?php

declare(strict_types=1);

namespace Fleet\Infrastructure\Persistence;

use Fleet\Domain\Brand;
use Fleet\Domain\BrandRepositoryInterface;
use Fleet\Infrastructure\Persistence\Models\BrandEloquentModel;

final class EloquentBrandRepository implements BrandRepositoryInterface
{
    public function findById(string $id): ?Brand
    {
        $model = BrandEloquentModel::find($id);

        return $model !== null ? $this->toDomain($model) : null;
    }

    public function findByName(string $name): ?Brand
    {
        $model = BrandEloquentModel::where('name', $name)->first();

        return $model !== null ? $this->toDomain($model) : null;
    }

    /**
     * @return Brand[]
     */
    public function findAll(): array
    {
        return BrandEloquentModel::orderBy('name')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->all();
    }

    public function save(Brand $brand): void
    {
        BrandEloquentModel::updateOrCreate(
            ['id' => $brand->id()],
            [
                'name' => $brand->name(),
            ]
        );
    }

    public function delete(string $id): void
    {
        BrandEloquentModel::destroy($id);
    }

    private function toDomain(BrandEloquentModel $model): Brand
    {
        return new Brand(
            id: $model->id,
            name: $model->name,
            createdAt: new \DateTimeImmutable($model->created_at),
            updatedAt: new \DateTimeImmutable($model->updated_at),
        );
    }
}
