<?php

declare(strict_types=1);

namespace Pricing\Application\ListDefaultPricingClasses;

use Pricing\Infrastructure\Persistence\Models\PricingClassEloquentModel;
use Tenant\Infrastructure\Persistence\Models\TenantEloquentModel;

/**
 * Handler pour récupérer les classes tarifaires du tenant par défaut.
 */
final class ListDefaultPricingClassesHandler
{
    private const DEFAULT_TENANT_SLUG = 'default';

    public function handle(ListDefaultPricingClassesQuery $query): ListDefaultPricingClassesResponse
    {
        $defaultTenant = TenantEloquentModel::where('slug', self::DEFAULT_TENANT_SLUG)->first();

        if ($defaultTenant === null) {
            return new ListDefaultPricingClassesResponse([]);
        }

        $pricingClasses = PricingClassEloquentModel::withoutGlobalScope('tenant')
            ->where('tenant_id', $defaultTenant->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $data = $pricingClasses->map(fn (PricingClassEloquentModel $model) => [
            'id' => $model->id,
            'code' => $model->code,
            'label' => $model->label,
            'description' => $model->description,
            'color' => $model->color,
            'sort_order' => $model->sort_order,
        ])->all();

        return new ListDefaultPricingClassesResponse($data);
    }
}
