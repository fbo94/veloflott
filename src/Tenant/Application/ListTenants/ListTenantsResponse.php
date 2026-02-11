<?php

declare(strict_types=1);

namespace Tenant\Application\ListTenants;

use Tenant\Domain\Tenant;

final readonly class ListTenantsResponse
{
    /**
     * @param  Tenant[]  $tenants
     */
    public function __construct(
        private array $tenants,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'tenants' => array_map(
                fn (Tenant $tenant) => [
                    'id' => $tenant->id(),
                    'name' => $tenant->name(),
                    'slug' => $tenant->slug(),
                    'address' => $tenant->address(),
                    'contact_email' => $tenant->contactEmail(),
                    'contact_phone' => $tenant->contactPhone(),
                    'logo_url' => $tenant->logoUrl(),
                    'status' => $tenant->status(),
                    'subscription_plan_id' => $tenant->subscriptionPlanId(),
                    'limits' => [
                        'max_users' => $tenant->maxUsers(),
                        'max_bikes' => $tenant->maxBikes(),
                        'max_sites' => $tenant->maxSites(),
                    ],
                    'trial' => [
                        'is_in_trial' => $tenant->isInTrial(),
                        'trial_ends_at' => $tenant->trialEndsAt()?->format('Y-m-d H:i:s'),
                    ],
                    'onboarding_completed' => $tenant->onboardingCompleted(),
                    'created_at' => $tenant->createdAt()->format('Y-m-d H:i:s'),
                ],
                $this->tenants
            ),
            'total' => count($this->tenants),
        ];
    }
}
