<?php

declare(strict_types=1);

namespace Tenant\Application\UpdateTenant;

final readonly class UpdateTenantResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public ?string $address,
        public ?string $contactEmail,
        public ?string $contactPhone,
        public ?string $logoUrl,
        public string $subscriptionPlanId,
        public string $subscriptionPlanName,
        public string $subscriptionPlanDisplayName,
        public int $maxUsers,
        public int $maxBikes,
        public int $maxSites,
        public string $status,
        public string $updatedAt,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'address' => $this->address,
            'contact_email' => $this->contactEmail,
            'contact_phone' => $this->contactPhone,
            'logo_url' => $this->logoUrl,
            'subscription_plan' => [
                'id' => $this->subscriptionPlanId,
                'name' => $this->subscriptionPlanName,
                'display_name' => $this->subscriptionPlanDisplayName,
            ],
            'limits' => [
                'max_users' => $this->maxUsers,
                'max_bikes' => $this->maxBikes,
                'max_sites' => $this->maxSites,
            ],
            'status' => $this->status,
            'updated_at' => $this->updatedAt,
        ];
    }
}
