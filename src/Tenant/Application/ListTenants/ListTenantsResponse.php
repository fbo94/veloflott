<?php

declare(strict_types=1);

namespace Tenant\Application\ListTenants;

use Subscription\Domain\SubscriptionPlanRepositoryInterface;
use Tenant\Domain\Tenant;

final readonly class ListTenantsResponse
{
    /**
     * @param  array<int, array{tenant: Tenant, bikes_count: int, sites_count: int, users_count: int}>  $tenantsWithStats
     */
    public function __construct(
        private array $tenantsWithStats,
        private SubscriptionPlanRepositoryInterface $subscriptionPlanRepository,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'tenants' => array_map(
                fn (array $data) => [
                    'id' => $data['tenant']->id(),
                    'name' => $data['tenant']->name(),
                    'slug' => $data['tenant']->slug(),
                    'address' => $data['tenant']->address(),
                    'contact_email' => $data['tenant']->contactEmail(),
                    'contact_phone' => $data['tenant']->contactPhone(),
                    'logo_url' => $data['tenant']->logoUrl(),
                    'status' => $data['tenant']->status(),
                    'subscription_plan' => $data['tenant']->subscriptionPlanId() !== null
                        ? $this->formatSubscriptionPlan($this->subscriptionPlanRepository->findById($data['tenant']->subscriptionPlanId()))
                        : null,
                    'limits' => [
                        'max_users' => $data['tenant']->maxUsers(),
                        'max_bikes' => $data['tenant']->maxBikes(),
                        'max_sites' => $data['tenant']->maxSites(),
                    ],
                    'usage' => [
                        'users_count' => $data['users_count'],
                        'bikes_count' => $data['bikes_count'],
                        'sites_count' => $data['sites_count'],
                    ],
                    'trial' => [
                        'is_in_trial' => $data['tenant']->isInTrial(),
                        'trial_ends_at' => $data['tenant']->trialEndsAt()?->format('Y-m-d H:i:s'),
                    ],
                    'onboarding_completed' => $data['tenant']->onboardingCompleted(),
                    'created_at' => $data['tenant']->createdAt()->format('Y-m-d H:i:s'),
                ],
                $this->tenantsWithStats
            ),
            'total' => count($this->tenantsWithStats),
        ];
    }

    /**
     * @param \Subscription\Domain\SubscriptionPlan|null $plan
     * @return array<string, mixed>|null
     */
    private function formatSubscriptionPlan($plan): ?array
    {
        if ($plan === null) {
            return null;
        }

        return [
            'id' => $plan->id(),
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
        ];
    }
}
