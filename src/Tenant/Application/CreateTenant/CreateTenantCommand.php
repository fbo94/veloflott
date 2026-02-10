<?php

declare(strict_types=1);

namespace Tenant\Application\CreateTenant;

final readonly class CreateTenantCommand
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $contactEmail,
        public string $contactPhone,
        public string $address,
        public ?string $logoUrl,
        public string $subscriptionPlanId,
    ) {}
}
