<?php

declare(strict_types=1);

namespace Pricing\Infrastructure\Persistence\Mappers;

use Pricing\Domain\DurationDefinition;
use Pricing\Infrastructure\Persistence\Models\DurationDefinitionEloquentModel;

final class DurationDefinitionMapper
{
    public static function toDomain(DurationDefinitionEloquentModel $model): DurationDefinition
    {
        return new DurationDefinition(
            id: $model->id,
            code: $model->code,
            label: $model->label,
            durationHours: $model->duration_hours,
            durationDays: $model->duration_days,
            isCustom: $model->is_custom,
            sortOrder: $model->sort_order,
            isActive: $model->is_active,
            deletedAt: $model->deleted_at instanceof \DateTimeInterface ? \DateTimeImmutable::createFromInterface($model->deleted_at) : null,
            createdAt: $model->created_at instanceof \DateTimeInterface ? \DateTimeImmutable::createFromInterface($model->created_at) : null,
            updatedAt: $model->updated_at instanceof \DateTimeInterface ? \DateTimeImmutable::createFromInterface($model->updated_at) : null,
        );
    }

    public static function toEloquent(DurationDefinition $duration): DurationDefinitionEloquentModel
    {
        $model = new DurationDefinitionEloquentModel;
        $model->id = $duration->id();
        $model->code = $duration->code();
        $model->label = $duration->label();
        $model->duration_hours = $duration->durationHours();
        $model->duration_days = $duration->durationDays();
        $model->is_custom = $duration->isCustom();
        $model->sort_order = $duration->sortOrder();
        $model->is_active = $duration->isActive();

        return $model;
    }

    public static function updateEloquent(DurationDefinition $duration, DurationDefinitionEloquentModel $model): void
    {
        $model->label = $duration->label();
        $model->duration_hours = $duration->durationHours();
        $model->duration_days = $duration->durationDays();
        $model->is_custom = $duration->isCustom();
        $model->sort_order = $duration->sortOrder();
        $model->is_active = $duration->isActive();
    }
}
