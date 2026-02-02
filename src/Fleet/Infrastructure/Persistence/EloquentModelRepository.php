<?php

declare(strict_types=1);

namespace Fleet\Infrastructure\Persistence;

use Fleet\Domain\Model;
use Fleet\Domain\ModelRepositoryInterface;
use Fleet\Infrastructure\Persistence\Models\ModelEloquentModel;

final class EloquentModelRepository implements ModelRepositoryInterface
{
    public function findById(string $id): ?Model
    {
        $model = ModelEloquentModel::find($id);

        return $model !== null ? $this->toDomain($model) : null;
    }

    public function findByName(string $name): ?Model
    {
        $model = ModelEloquentModel::where('name', $name)->first();

        return $model !== null ? $this->toDomain($model) : null;
    }

    /**
     * @return Model[]
     */
    public function findAll(): array
    {
        return ModelEloquentModel::orderBy('name')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->all();
    }

    /**
     * @return Model[]
     */
    public function findByBrandId(string $brandId): array
    {
        return ModelEloquentModel::where('brand_id', $brandId)
            ->orderBy('name')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->all();
    }

    public function countByBrandId(string $brandId): int
    {
        return ModelEloquentModel::where('brand_id', $brandId)->count();
    }

    public function save(Model $model): void
    {
        ModelEloquentModel::updateOrCreate(
            ['id' => $model->id()],
            [
                'name' => $model->name(),
                'brand_id' => $model->brandId(),
            ]
        );
    }

    public function delete(string $id): void
    {
        ModelEloquentModel::destroy($id);
    }

    private function toDomain(ModelEloquentModel $model): Model
    {
        return new Model(
            id: $model->id,
            name: $model->name,
            brandId: $model->brand_id,
            createdAt: new \DateTimeImmutable($model->created_at),
            updatedAt: new \DateTimeImmutable($model->updated_at),
        );
    }
}
