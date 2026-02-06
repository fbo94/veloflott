<?php

declare(strict_types=1);

namespace Fleet\Infrastructure\Persistence;

use Fleet\Domain\DurationDefinition;
use Fleet\Domain\DurationDefinitionRepositoryInterface;
use Fleet\Infrastructure\Persistence\Mappers\DurationDefinitionMapper;
use Fleet\Infrastructure\Persistence\Models\DurationDefinitionEloquentModel;

final class EloquentDurationDefinitionRepository implements DurationDefinitionRepositoryInterface
{
    public function save(DurationDefinition $duration): void
    {
        $model = DurationDefinitionEloquentModel::find($duration->id());

        if ($model === null) {
            $model = DurationDefinitionMapper::toEloquent($duration);
        } else {
            DurationDefinitionMapper::updateEloquent($duration, $model);
        }

        $model->save();
    }

    public function findById(string $id): ?DurationDefinition
    {
        $model = DurationDefinitionEloquentModel::find($id);

        return $model !== null ? DurationDefinitionMapper::toDomain($model) : null;
    }

    public function findByCode(string $code): ?DurationDefinition
    {
        $model = DurationDefinitionEloquentModel::where('code', $code)->first();

        return $model !== null ? DurationDefinitionMapper::toDomain($model) : null;
    }

    /**
     * @return DurationDefinition[]
     */
    public function findAllActive(): array
    {
        return DurationDefinitionEloquentModel::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($model) => DurationDefinitionMapper::toDomain($model))
            ->all();
    }

    /**
     * @return DurationDefinition[]
     */
    public function findAll(): array
    {
        return DurationDefinitionEloquentModel::orderBy('sort_order')
            ->get()
            ->map(fn ($model) => DurationDefinitionMapper::toDomain($model))
            ->all();
    }

    public function delete(string $id): void
    {
        DurationDefinitionEloquentModel::where('id', $id)->delete();
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
