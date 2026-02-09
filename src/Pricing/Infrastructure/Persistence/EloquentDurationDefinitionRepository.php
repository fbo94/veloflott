<?php

declare(strict_types=1);

namespace Pricing\Infrastructure\Persistence;

use Pricing\Domain\DurationDefinition;
use Pricing\Domain\DurationDefinitionRepositoryInterface;
use Pricing\Infrastructure\Persistence\Mappers\DurationDefinitionMapper;
use Pricing\Infrastructure\Persistence\Models\DurationDefinitionEloquentModel;

final class EloquentDurationDefinitionRepository implements DurationDefinitionRepositoryInterface
{
    public function save(DurationDefinition $duration): void
    {
        $model = DurationDefinitionEloquentModel::find($duration->id());

        if ($model === null) {
            $model = DurationDefinitionMapper::toEloquent($duration);
            $model->save();
        } else {
            DurationDefinitionMapper::updateEloquent($duration, $model);
            $model->save();
        }
    }

    public function findById(string $id): ?DurationDefinition
    {
        $model = DurationDefinitionEloquentModel::find($id);

        if ($model === null) {
            return null;
        }

        return DurationDefinitionMapper::toDomain($model);
    }

    public function findByCode(string $code): ?DurationDefinition
    {
        $model = DurationDefinitionEloquentModel::where('code', $code)->first();

        if ($model === null) {
            return null;
        }

        return DurationDefinitionMapper::toDomain($model);
    }

    /**
     * @return DurationDefinition[]
     */
    public function findAllActive(): array
    {
        $models = DurationDefinitionEloquentModel::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return $models->map(fn ($model) => DurationDefinitionMapper::toDomain($model))->all();
    }

    /**
     * @return DurationDefinition[]
     */
    public function findAll(): array
    {
        $models = DurationDefinitionEloquentModel::orderBy('sort_order')->get();

        return $models->map(fn ($model) => DurationDefinitionMapper::toDomain($model))->all();
    }

    public function delete(string $id): void
    {
        DurationDefinitionEloquentModel::destroy($id);
    }

    public function existsWithCode(string $code, ?string $excludeId = null): bool
    {
        $query = DurationDefinitionEloquentModel::where('code', $code);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
