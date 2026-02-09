<?php

declare(strict_types=1);

namespace Pricing\Infrastructure\Persistence\Mappers;

use Pricing\Domain\PricingRate;
use Pricing\Infrastructure\Persistence\Models\PricingRateEloquentModel;

final class PricingRateMapper
{
    public static function toDomain(PricingRateEloquentModel $model): PricingRate
    {
        return new PricingRate(
            id: $model->id,
            categoryId: $model->category_id,
            pricingClassId: $model->pricing_class_id,
            durationId: $model->duration_id,
            price: $model->price,
            isActive: $model->is_active,
            deletedAt: $model->deleted_at instanceof \DateTimeInterface ? \DateTimeImmutable::createFromInterface($model->deleted_at) : null,
            createdAt: $model->created_at instanceof \DateTimeInterface ? \DateTimeImmutable::createFromInterface($model->created_at) : null,
            updatedAt: $model->updated_at instanceof \DateTimeInterface ? \DateTimeImmutable::createFromInterface($model->updated_at) : null,
        );
    }

    public static function toEloquent(PricingRate $rate): PricingRateEloquentModel
    {
        $model = new PricingRateEloquentModel;
        $model->id = $rate->id();
        $model->category_id = $rate->categoryId();
        $model->pricing_class_id = $rate->pricingClassId();
        $model->duration_id = $rate->durationId();
        $model->price = $rate->price();
        $model->is_active = $rate->isActive();

        return $model;
    }

    public static function updateEloquent(PricingRate $rate, PricingRateEloquentModel $model): void
    {
        $model->price = $rate->price();
        $model->is_active = $rate->isActive();
    }
}
