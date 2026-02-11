<?php

declare(strict_types=1);

namespace Pricing\Application\ListDefaultPricingRates;

use Pricing\Infrastructure\Persistence\Models\PricingRateEloquentModel;
use Tenant\Infrastructure\Persistence\Models\TenantEloquentModel;

/**
 * Handler pour récupérer les tarifs du tenant par défaut.
 */
final class ListDefaultPricingRatesHandler
{
    private const DEFAULT_TENANT_SLUG = 'default';

    public function handle(ListDefaultPricingRatesQuery $query): ListDefaultPricingRatesResponse
    {
        $defaultTenant = TenantEloquentModel::where('slug', self::DEFAULT_TENANT_SLUG)->first();

        if ($defaultTenant === null) {
            return new ListDefaultPricingRatesResponse([]);
        }

        $ratesQuery = PricingRateEloquentModel::withoutGlobalScope('tenant')
            ->where('tenant_id', $defaultTenant->id)
            ->where('is_active', true);

        if ($query->categoryId !== null) {
            $ratesQuery->where('category_id', $query->categoryId);
        }

        if ($query->pricingClassId !== null) {
            $ratesQuery->where('pricing_class_id', $query->pricingClassId);
        }

        $rates = $ratesQuery->get();

        $data = $rates->map(fn (PricingRateEloquentModel $model) => [
            'id' => $model->id,
            'category_id' => $model->category_id,
            'pricing_class_id' => $model->pricing_class_id,
            'duration_id' => $model->duration_id,
            'price' => $model->price,
            'is_active' => $model->is_active,
        ])->all();

        return new ListDefaultPricingRatesResponse($data);
    }
}
