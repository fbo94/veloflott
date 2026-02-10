<?php

declare(strict_types=1);

namespace Subscription\Application\ListSubscriptionPlans;

use Subscription\Domain\SubscriptionPlan;

final readonly class ListSubscriptionPlansResponse
{
    /**
     * @param SubscriptionPlan[] $plans
     */
    public function __construct(
        private array $plans,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'subscription_plans' => array_map(
                fn (SubscriptionPlan $plan) => [
                    'id' => $plan->id(),
                    'name' => $plan->name(),
                    'display_name' => $plan->displayName(),
                    'description' => $plan->description(),
                    'price_monthly' => $plan->priceMonthly(),
                    'price_yearly' => $plan->priceYearly(),
                    'limits' => [
                        'max_users' => $plan->maxUsers(),
                        'max_bikes' => $plan->maxBikes(),
                        'max_sites' => $plan->maxSites(),
                    ],
                    'features' => $plan->features(),
                    'sort_order' => $plan->sortOrder(),
                ],
                $this->plans
            ),
        ];
    }
}
