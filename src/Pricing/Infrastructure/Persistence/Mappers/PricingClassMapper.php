<?php

declare(strict_types=1);

namespace Pricing\Infrastructure\Persistence\Mappers;

use Pricing\Domain\PricingClass;
use Pricing\Infrastructure\Persistence\Models\PricingClassEloquentModel;

final class PricingClassMapper
{
    public static function toDomain(PricingClassEloquentModel $model): PricingClass
    {
        return new PricingClass(
            id: $model->id,
            code: $model->code,
            label: $model->label,
            description: $model->description,
            color: $model->color,
            sortOrder: $model->sort_order,
            isActive: $model->is_active,
            deletedAt: $model->deleted_at instanceof \DateTimeInterface ? \DateTimeImmutable::createFromInterface($model->deleted_at) : null,
            createdAt: $model->created_at instanceof \DateTimeInterface ? \DateTimeImmutable::createFromInterface($model->created_at) : null,
            updatedAt: $model->updated_at instanceof \DateTimeInterface ? \DateTimeImmutable::createFromInterface($model->updated_at) : null,
        );
    }

    public static function toEloquent(PricingClass $pricingClass): PricingClassEloquentModel
    {
        $model = new PricingClassEloquentModel;
        $model->id = $pricingClass->id();
        $model->code = $pricingClass->code();
        $model->label = $pricingClass->label();
        $model->description = $pricingClass->description();
        $model->color = $pricingClass->color();
        $model->sort_order = $pricingClass->sortOrder();
        $model->is_active = $pricingClass->isActive();
        $model->deleted_at = $pricingClass->deletedAt()?->format('Y-m-d H:i:s');
        $model->created_at = $pricingClass->createdAt()->format('Y-m-d H:i:s');
        $model->updated_at = $pricingClass->updatedAt()->format('Y-m-d H:i:s');

        return $model;
    }

    public static function updateEloquent(PricingClass $pricingClass, PricingClassEloquentModel $model): void
    {
        $model->code = $pricingClass->code();
        $model->label = $pricingClass->label();
        $model->description = $pricingClass->description();
        $model->color = $pricingClass->color();
        $model->sort_order = $pricingClass->sortOrder();
        $model->is_active = $pricingClass->isActive();
        $model->deleted_at = $pricingClass->deletedAt()?->format('Y-m-d H:i:s');
        $model->updated_at = $pricingClass->updatedAt()->format('Y-m-d H:i:s');
    }
}
