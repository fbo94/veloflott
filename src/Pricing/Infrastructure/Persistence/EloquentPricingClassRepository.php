<?php

declare(strict_types=1);

namespace Pricing\Infrastructure\Persistence;

use Pricing\Domain\PricingClass;
use Pricing\Domain\PricingClassRepositoryInterface;
use Pricing\Infrastructure\Persistence\Mappers\PricingClassMapper;
use Pricing\Infrastructure\Persistence\Models\PricingClassEloquentModel;

final class EloquentPricingClassRepository implements PricingClassRepositoryInterface
{
    public function save(PricingClass $pricingClass): void
    {
        $model = PricingClassEloquentModel::find($pricingClass->id());

        if ($model === null) {
            $model = PricingClassMapper::toEloquent($pricingClass);
            $model->save();
        } else {
            PricingClassMapper::updateEloquent($pricingClass, $model);
            $model->save();
        }
    }

    public function findById(string $id): ?PricingClass
    {
        $model = PricingClassEloquentModel::find($id);

        if ($model === null) {
            return null;
        }

        return PricingClassMapper::toDomain($model);
    }

    public function findByCode(string $code): ?PricingClass
    {
        $model = PricingClassEloquentModel::where('code', $code)->first();

        if ($model === null) {
            return null;
        }

        return PricingClassMapper::toDomain($model);
    }

    /**
     * @return PricingClass[]
     */
    public function findAllActive(): array
    {
        $models = PricingClassEloquentModel::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return $models->map(fn ($model) => PricingClassMapper::toDomain($model))->all();
    }

    /**
     * @return PricingClass[]
     */
    public function findAll(): array
    {
        $models = PricingClassEloquentModel::orderBy('sort_order')->get();

        return $models->map(fn ($model) => PricingClassMapper::toDomain($model))->all();
    }

    public function delete(string $id): void
    {
        PricingClassEloquentModel::destroy($id);
    }

    public function existsWithCode(string $code, ?string $excludeId = null): bool
    {
        $query = PricingClassEloquentModel::where('code', $code);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
