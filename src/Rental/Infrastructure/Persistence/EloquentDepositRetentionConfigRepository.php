<?php

declare(strict_types=1);

namespace Rental\Infrastructure\Persistence;

use Rental\Domain\DepositRetentionConfig;
use Rental\Domain\DepositRetentionConfigId;
use Rental\Domain\Repository\DepositRetentionConfigRepositoryInterface;
use Rental\Infrastructure\Persistence\Models\DepositRetentionConfigEloquentModel;

final class EloquentDepositRetentionConfigRepository implements DepositRetentionConfigRepositoryInterface
{
    public function save(DepositRetentionConfig $config): void
    {
        DepositRetentionConfigEloquentModel::updateOrCreate(
            ['id' => $config->id()->value()],
            [
                'bike_id' => $config->bikeId(),
                'pricing_class_id' => $config->pricingClassId(),
                'category_id' => $config->categoryId(),
                'minor_damage_amount' => $config->minorDamageAmount(),
                'major_damage_amount' => $config->majorDamageAmount(),
                'total_loss_amount' => $config->totalLossAmount(),
            ],
        );
    }

    public function findById(DepositRetentionConfigId $id): ?DepositRetentionConfig
    {
        $model = DepositRetentionConfigEloquentModel::find($id->value());

        return $model ? $this->toDomain($model) : null;
    }

    public function findByBikeId(string $bikeId): ?DepositRetentionConfig
    {
        $model = DepositRetentionConfigEloquentModel::where('bike_id', $bikeId)->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findByPricingClassId(string $pricingClassId): ?DepositRetentionConfig
    {
        $model = DepositRetentionConfigEloquentModel::where('pricing_class_id', $pricingClassId)->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findByCategoryId(string $categoryId): ?DepositRetentionConfig
    {
        $model = DepositRetentionConfigEloquentModel::where('category_id', $categoryId)->first();

        return $model ? $this->toDomain($model) : null;
    }

    /**
     * @return DepositRetentionConfig[]
     */
    public function findAll(): array
    {
        return DepositRetentionConfigEloquentModel::all()
            ->map(fn (DepositRetentionConfigEloquentModel $model) => $this->toDomain($model))
            ->all();
    }

    public function delete(DepositRetentionConfigId $id): void
    {
        DepositRetentionConfigEloquentModel::destroy($id->value());
    }

    private function toDomain(DepositRetentionConfigEloquentModel $model): DepositRetentionConfig
    {
        return DepositRetentionConfig::reconstitute(
            id: new DepositRetentionConfigId($model->id),
            bikeId: $model->bike_id,
            pricingClassId: $model->pricing_class_id,
            categoryId: $model->category_id,
            minorDamageAmount: $model->minor_damage_amount,
            majorDamageAmount: $model->major_damage_amount,
            totalLossAmount: $model->total_loss_amount,
            createdAt: \DateTimeImmutable::createFromInterface($model->created_at),
            updatedAt: \DateTimeImmutable::createFromInterface($model->updated_at),
        );
    }
}
