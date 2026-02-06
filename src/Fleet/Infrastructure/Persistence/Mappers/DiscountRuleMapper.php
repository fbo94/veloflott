<?php

declare(strict_types=1);

namespace Fleet\Infrastructure\Persistence\Mappers;

use Fleet\Domain\DiscountRule;
use Fleet\Domain\DiscountType;
use Fleet\Infrastructure\Persistence\Models\DiscountRuleEloquentModel;

final class DiscountRuleMapper
{
    public static function toDomain(DiscountRuleEloquentModel $model): DiscountRule
    {
        return new DiscountRule(
            id: $model->id,
            categoryId: $model->category_id,
            pricingClassId: $model->pricing_class_id,
            minDays: $model->min_days,
            minDurationId: $model->min_duration_id,
            discountType: DiscountType::from($model->discount_type),
            discountValue: $model->discount_value,
            label: $model->label,
            description: $model->description,
            isCumulative: $model->is_cumulative,
            priority: $model->priority,
            isActive: $model->is_active,
            deletedAt: $model->deleted_at instanceof \DateTimeInterface ? \DateTimeImmutable::createFromInterface($model->deleted_at) : null,
            createdAt: $model->created_at instanceof \DateTimeInterface ? \DateTimeImmutable::createFromInterface($model->created_at) : null,
            updatedAt: $model->updated_at instanceof \DateTimeInterface ? \DateTimeImmutable::createFromInterface($model->updated_at) : null,
        );
    }

    public static function toEloquent(DiscountRule $rule): DiscountRuleEloquentModel
    {
        $model = new DiscountRuleEloquentModel;
        $model->id = $rule->id();
        $model->category_id = $rule->categoryId();
        $model->pricing_class_id = $rule->pricingClassId();
        $model->min_days = $rule->minDays();
        $model->min_duration_id = $rule->minDurationId();
        $model->discount_type = $rule->discountType()->value;
        $model->discount_value = $rule->discountValue();
        $model->label = $rule->label();
        $model->description = $rule->description();
        $model->is_cumulative = $rule->isCumulative();
        $model->priority = $rule->priority();
        $model->is_active = $rule->isActive();
        $model->deleted_at = $rule->deletedAt()?->format('Y-m-d H:i:s');
        $model->created_at = $rule->createdAt()->format('Y-m-d H:i:s');
        $model->updated_at = $rule->updatedAt()->format('Y-m-d H:i:s');

        return $model;
    }

    public static function updateEloquent(DiscountRule $rule, DiscountRuleEloquentModel $model): void
    {
        $model->category_id = $rule->categoryId();
        $model->pricing_class_id = $rule->pricingClassId();
        $model->min_days = $rule->minDays();
        $model->min_duration_id = $rule->minDurationId();
        $model->discount_type = $rule->discountType()->value;
        $model->discount_value = $rule->discountValue();
        $model->label = $rule->label();
        $model->description = $rule->description();
        $model->is_cumulative = $rule->isCumulative();
        $model->priority = $rule->priority();
        $model->is_active = $rule->isActive();
        $model->deleted_at = $rule->deletedAt()?->format('Y-m-d H:i:s');
        $model->updated_at = $rule->updatedAt()->format('Y-m-d H:i:s');
    }
}
