<?php

declare(strict_types=1);

namespace Pricing\Infrastructure\Persistence;

use Pricing\Domain\DiscountRule;
use Pricing\Domain\DiscountRuleRepositoryInterface;
use Pricing\Infrastructure\Persistence\Mappers\DiscountRuleMapper;
use Pricing\Infrastructure\Persistence\Models\DiscountRuleEloquentModel;

final class EloquentDiscountRuleRepository implements DiscountRuleRepositoryInterface
{
    public function save(DiscountRule $rule): void
    {
        $model = DiscountRuleEloquentModel::find($rule->id());

        if ($model === null) {
            $model = DiscountRuleMapper::toEloquent($rule);
            $model->save();
        } else {
            DiscountRuleMapper::updateEloquent($rule, $model);
            $model->save();
        }
    }

    public function findById(string $id): ?DiscountRule
    {
        $model = DiscountRuleEloquentModel::find($id);

        if ($model === null) {
            return null;
        }

        return DiscountRuleMapper::toDomain($model);
    }

    /**
     * @return DiscountRule[]
     */
    public function findAllActive(): array
    {
        $models = DiscountRuleEloquentModel::where('is_active', true)
            ->orderBy('priority')
            ->get();

        return $models->map(fn ($model) => DiscountRuleMapper::toDomain($model))->all();
    }

    /**
     * @return DiscountRule[]
     */
    public function findApplicableRules(
        string $categoryId,
        string $pricingClassId,
        int $days
    ): array {
        $models = DiscountRuleEloquentModel::where('is_active', true)
            ->where(function ($query) use ($categoryId) {
                $query->whereNull('category_id')
                    ->orWhere('category_id', $categoryId);
            })
            ->where(function ($query) use ($pricingClassId) {
                $query->whereNull('pricing_class_id')
                    ->orWhere('pricing_class_id', $pricingClassId);
            })
            ->where(function ($query) use ($days) {
                $query->whereNull('min_days')
                    ->orWhere('min_days', '<=', $days);
            })
            ->orderBy('priority')
            ->get();

        return $models->map(fn ($model) => DiscountRuleMapper::toDomain($model))->all();
    }

    public function delete(string $id): void
    {
        DiscountRuleEloquentModel::destroy($id);
    }
}
