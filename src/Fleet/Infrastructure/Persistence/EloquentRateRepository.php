<?php

declare(strict_types=1);

namespace Fleet\Infrastructure\Persistence;

use Fleet\Domain\Rate;
use Fleet\Domain\RateRepositoryInterface;
use Fleet\Infrastructure\Persistence\Models\RateEloquentModel;

final class EloquentRateRepository implements RateRepositoryInterface
{
    public function findById(string $id): ?Rate
    {
        $model = RateEloquentModel::find($id);

        return $model !== null ? $this->toDomain($model) : null;
    }

    public function findByCategoryId(string $categoryId): ?Rate
    {
        $model = RateEloquentModel::where('category_id', $categoryId)->first();

        return $model !== null ? $this->toDomain($model) : null;
    }

    public function findByBikeId(string $bikeId): ?Rate
    {
        $model = RateEloquentModel::where('bike_id', $bikeId)->first();

        return $model !== null ? $this->toDomain($model) : null;
    }

    /**
     * @return Rate[]
     */
    public function findAllCategoryRates(): array
    {
        return RateEloquentModel::whereNotNull('category_id')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->all();
    }

    public function save(Rate $rate): void
    {
        RateEloquentModel::updateOrCreate(
            ['id' => $rate->id()],
            [
                'category_id' => $rate->categoryId(),
                'bike_id' => $rate->bikeId(),
                'half_day_price' => $rate->halfDayPrice(),
                'day_price' => $rate->dayPrice(),
                'weekend_price' => $rate->weekendPrice(),
                'week_price' => $rate->weekPrice(),
            ]
        );
    }

    public function delete(Rate $rate): void
    {
        RateEloquentModel::where('id', $rate->id())->delete();
    }

    private function toDomain(RateEloquentModel $model): Rate
    {
        return new Rate(
            id: $model->id,
            categoryId: $model->category_id,
            bikeId: $model->bike_id,
            halfDayPrice: $model->half_day_price,
            dayPrice: $model->day_price,
            weekendPrice: $model->weekend_price,
            weekPrice: $model->week_price,
            createdAt: \DateTimeImmutable::createFromInterface($model->created_at),
            updatedAt: \DateTimeImmutable::createFromInterface($model->updated_at),
        );
    }
}
