<?php

declare(strict_types=1);

namespace Fleet\Infrastructure\Persistence;

use Fleet\Domain\PricingRate;
use Fleet\Domain\PricingRateRepositoryInterface;
use Fleet\Infrastructure\Persistence\Mappers\PricingRateMapper;
use Fleet\Infrastructure\Persistence\Models\PricingRateEloquentModel;

final class EloquentPricingRateRepository implements PricingRateRepositoryInterface
{
    public function save(PricingRate $rate): void
    {
        $model = PricingRateEloquentModel::find($rate->id());

        if ($model === null) {
            $model = PricingRateMapper::toEloquent($rate);
        } else {
            PricingRateMapper::updateEloquent($rate, $model);
        }

        $model->save();
    }

    public function findById(string $id): ?PricingRate
    {
        $model = PricingRateEloquentModel::find($id);

        return $model !== null ? PricingRateMapper::toDomain($model) : null;
    }

    public function findByDimensions(
        string $categoryId,
        string $pricingClassId,
        string $durationId
    ): ?PricingRate {
        $model = PricingRateEloquentModel::where('category_id', $categoryId)
            ->where('pricing_class_id', $pricingClassId)
            ->where('duration_id', $durationId)
            ->first();

        return $model !== null ? PricingRateMapper::toDomain($model) : null;
    }

    /**
     * @return PricingRate[]
     */
    public function findByCategoryAndClass(
        ?string $categoryId = null,
        ?string $pricingClassId = null
    ): array {
        $query = PricingRateEloquentModel::query();

        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }

        if ($pricingClassId !== null) {
            $query->where('pricing_class_id', $pricingClassId);
        }

        return $query->get()
            ->map(fn ($model) => PricingRateMapper::toDomain($model))
            ->all();
    }

    /**
     * @return PricingRate[]
     */
    public function findAllActive(): array
    {
        return PricingRateEloquentModel::where('is_active', true)
            ->get()
            ->map(fn ($model) => PricingRateMapper::toDomain($model))
            ->all();
    }

    /**
     * @return PricingRate[]
     */
    public function findAll(): array
    {
        return PricingRateEloquentModel::all()
            ->map(fn ($model) => PricingRateMapper::toDomain($model))
            ->all();
    }

    /**
     * @return PricingRate[]
     */
    public function findByCategory(string $categoryId): array
    {
        return PricingRateEloquentModel::where('category_id', $categoryId)
            ->get()
            ->map(fn ($model) => PricingRateMapper::toDomain($model))
            ->all();
    }

    public function delete(string $id): void
    {
        PricingRateEloquentModel::where('id', $id)->delete();
    }
}
