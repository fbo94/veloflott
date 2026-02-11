<?php

declare(strict_types=1);

namespace Pricing\Application\ListDefaultDurations;

use Pricing\Infrastructure\Persistence\Models\DurationDefinitionEloquentModel;
use Tenant\Infrastructure\Persistence\Models\TenantEloquentModel;

/**
 * Handler pour récupérer les durées du tenant par défaut.
 */
final class ListDefaultDurationsHandler
{
    private const DEFAULT_TENANT_SLUG = 'default';

    public function handle(ListDefaultDurationsQuery $query): ListDefaultDurationsResponse
    {
        $defaultTenant = TenantEloquentModel::where('slug', self::DEFAULT_TENANT_SLUG)->first();

        if ($defaultTenant === null) {
            return new ListDefaultDurationsResponse([]);
        }

        $durations = DurationDefinitionEloquentModel::withoutGlobalScope('tenant')
            ->where('tenant_id', $defaultTenant->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $data = $durations->map(fn (DurationDefinitionEloquentModel $model) => [
            'id' => $model->id,
            'code' => $model->code,
            'label' => $model->label,
            'duration_hours' => $model->duration_hours,
            'duration_days' => $model->duration_days,
            'is_custom' => $model->is_custom,
            'sort_order' => $model->sort_order,
        ])->all();

        return new ListDefaultDurationsResponse($data);
    }
}
