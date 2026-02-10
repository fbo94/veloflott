<?php

declare(strict_types=1);

namespace Subscription\Infrastructure\Persistence;

use Subscription\Domain\SubscriptionPlan;
use Subscription\Domain\SubscriptionPlanRepositoryInterface;
use Subscription\Infrastructure\Persistence\Models\SubscriptionPlanEloquentModel;

final class EloquentSubscriptionPlanRepository implements SubscriptionPlanRepositoryInterface
{
    public function findById(string $id): ?SubscriptionPlan
    {
        $model = SubscriptionPlanEloquentModel::find($id);

        return $model !== null ? $this->toDomain($model) : null;
    }

    public function findByName(string $name): ?SubscriptionPlan
    {
        $model = SubscriptionPlanEloquentModel::where('name', $name)->first();

        return $model !== null ? $this->toDomain($model) : null;
    }

    /**
     * @return SubscriptionPlan[]
     */
    public function findAllActive(): array
    {
        return SubscriptionPlanEloquentModel::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (SubscriptionPlanEloquentModel $model) => $this->toDomain($model))
            ->all();
    }

    /**
     * @return SubscriptionPlan[]
     */
    public function findAll(): array
    {
        return SubscriptionPlanEloquentModel::orderBy('sort_order')
            ->get()
            ->map(fn (SubscriptionPlanEloquentModel $model) => $this->toDomain($model))
            ->all();
    }

    public function save(SubscriptionPlan $plan): void
    {
        SubscriptionPlanEloquentModel::updateOrCreate(
            ['id' => $plan->id()],
            [
                'name' => $plan->name(),
                'display_name' => $plan->displayName(),
                'description' => $plan->description(),
                'price_monthly' => $plan->priceMonthly(),
                'price_yearly' => $plan->priceYearly(),
                'max_users' => $plan->maxUsers(),
                'max_bikes' => $plan->maxBikes(),
                'max_sites' => $plan->maxSites(),
                'features' => $plan->features(),
                'is_active' => $plan->isActive(),
                'sort_order' => $plan->sortOrder(),
            ]
        );
    }

    public function delete(string $id): void
    {
        SubscriptionPlanEloquentModel::destroy($id);
    }

    private function toDomain(SubscriptionPlanEloquentModel $model): SubscriptionPlan
    {
        return new SubscriptionPlan(
            id: $model->id,
            name: $model->name,
            displayName: $model->display_name,
            description: $model->description,
            priceMonthly: $model->price_monthly !== null ? (float) $model->price_monthly : null,
            priceYearly: $model->price_yearly !== null ? (float) $model->price_yearly : null,
            maxUsers: $model->max_users,
            maxBikes: $model->max_bikes,
            maxSites: $model->max_sites,
            features: $model->features,
            isActive: $model->is_active,
            sortOrder: $model->sort_order,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }
}
