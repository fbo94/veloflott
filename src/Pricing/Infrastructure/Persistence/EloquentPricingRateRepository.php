<?php

declare(strict_types=1);

namespace Pricing\Infrastructure\Persistence;

use Pricing\Domain\PricingRate;
use Pricing\Domain\PricingRateRepositoryInterface;
use Pricing\Infrastructure\Persistence\Mappers\PricingRateMapper;
use Pricing\Infrastructure\Persistence\Models\PricingRateEloquentModel;

final class EloquentPricingRateRepository implements PricingRateRepositoryInterface
{
    public function save(PricingRate $rate): void
    {
        $model = PricingRateEloquentModel::find($rate->id());

        if ($model === null) {
            $model = PricingRateMapper::toEloquent($rate);
            $model->save();
        } else {
            PricingRateMapper::updateEloquent($rate, $model);
            $model->save();
        }
    }

    public function findById(string $id): ?PricingRate
    {
        $model = PricingRateEloquentModel::find($id);

        if ($model === null) {
            return null;
        }

        return PricingRateMapper::toDomain($model);
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

        if ($model === null) {
            return null;
        }

        return PricingRateMapper::toDomain($model);
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

        $models = $query->get();

        return $models->map(fn ($model) => PricingRateMapper::toDomain($model))->all();
    }

    /**
     * @return PricingRate[]
     */
    public function findAllActive(): array
    {
        $models = PricingRateEloquentModel::where('is_active', true)->get();

        return $models->map(fn ($model) => PricingRateMapper::toDomain($model))->all();
    }

    /**
     * @return PricingRate[]
     */
    public function findAll(): array
    {
        $models = PricingRateEloquentModel::all();

        return $models->map(fn ($model) => PricingRateMapper::toDomain($model))->all();
    }

    /**
     * @return PricingRate[]
     */
    public function findByCategory(string $categoryId): array
    {
        $models = PricingRateEloquentModel::where('category_id', $categoryId)->get();

        return $models->map(fn ($model) => PricingRateMapper::toDomain($model))->all();
    }

    public function delete(string $id): void
    {
        PricingRateEloquentModel::destroy($id);
    }
}
