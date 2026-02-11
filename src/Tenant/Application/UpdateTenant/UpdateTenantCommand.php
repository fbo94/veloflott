<?php

declare(strict_types=1);

namespace Tenant\Application\UpdateTenant;

final readonly class UpdateTenantCommand
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public ?string $contactEmail,
        public ?string $contactPhone,
        public ?string $address,
        public ?string $logoUrl,
        public string $subscriptionPlanId,
        public int $maxUsers,
        public int $maxBikes,
        public int $maxSites,
    ) {
    }
}
