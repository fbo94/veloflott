<?php

declare(strict_types=1);

namespace Fleet\Infrastructure\Persistence;

use Fleet\Domain\DiscountRule;
use Fleet\Domain\DiscountRuleRepositoryInterface;
use Fleet\Infrastructure\Persistence\Mappers\DiscountRuleMapper;
use Fleet\Infrastructure\Persistence\Models\DiscountRuleEloquentModel;

final class EloquentDiscountRuleRepository implements DiscountRuleRepositoryInterface
{
    public function save(DiscountRule $rule): void
    {
        $model = DiscountRuleEloquentModel::find($rule->id());

        if ($model === null) {
            $model = DiscountRuleMapper::toEloquent($rule);
        } else {
            DiscountRuleMapper::updateEloquent($rule, $model);
        }

        $model->save();
    }

    public function findById(string $id): ?DiscountRule
    {
        $model = DiscountRuleEloquentModel::find($id);

        return $model !== null ? DiscountRuleMapper::toDomain($model) : null;
    }

    /**
     * @return DiscountRule[]
     */
    public function findAllActive(): array
    {
        return DiscountRuleEloquentModel::where('is_active', true)
            ->orderBy('priority')
            ->get()
            ->map(fn ($model) => DiscountRuleMapper::toDomain($model))
            ->all();
    }

    /**
     * @return DiscountRule[]
     */
    public function findApplicableRules(
        string $categoryId,
        string $pricingClassId,
        int $days
    ): array {
        return DiscountRuleEloquentModel::where('is_active', true)
            ->where(function ($query) use ($categoryId) {
                $query->whereNull('category_id')
                    ->orWhere('category_id', $categoryId);
            })
            ->where(function ($query) use ($pricingClassId) {
                $query->whereNull('pricing_class_id')
                    ->orWhere('pricing_class_id', $pricingClassId);
            })
            ->where(function ($query) use ($days) {
                $query->where('min_days', '<=', $days)
                    ->orWhereNull('min_days');
            })
            ->orderBy('priority')
            ->get()
            ->map(fn ($model) => DiscountRuleMapper::toDomain($model))
            ->all();
    }

    public function delete(string $id): void
    {
        DiscountRuleEloquentModel::where('id', $id)->delete();
    }
}
