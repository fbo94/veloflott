<?php

declare(strict_types=1);

namespace Pricing\Application\ListDefaultDiscountRules;

use Pricing\Infrastructure\Persistence\Models\DiscountRuleEloquentModel;
use Tenant\Infrastructure\Persistence\Models\TenantEloquentModel;

/**
 * Handler pour récupérer les règles de réduction du tenant par défaut.
 */
final class ListDefaultDiscountRulesHandler
{
    private const DEFAULT_TENANT_SLUG = 'default';

    public function handle(ListDefaultDiscountRulesQuery $query): ListDefaultDiscountRulesResponse
    {
        $defaultTenant = TenantEloquentModel::where('slug', self::DEFAULT_TENANT_SLUG)->first();

        if ($defaultTenant === null) {
            return new ListDefaultDiscountRulesResponse([]);
        }

        $discountRules = DiscountRuleEloquentModel::withoutGlobalScope('tenant')
            ->where('tenant_id', $defaultTenant->id)
            ->where('is_active', true)
            ->orderBy('priority')
            ->get();

        $data = $discountRules->map(fn (DiscountRuleEloquentModel $model) => [
            'id' => $model->id,
            'category_id' => $model->category_id,
            'pricing_class_id' => $model->pricing_class_id,
            'min_days' => $model->min_days,
            'min_duration_id' => $model->min_duration_id,
            'discount_type' => $model->discount_type,
            'discount_value' => $model->discount_value,
            'label' => $model->label,
            'description' => $model->description,
            'is_cumulative' => $model->is_cumulative,
            'priority' => $model->priority,
        ])->all();

        return new ListDefaultDiscountRulesResponse($data);
    }
}
