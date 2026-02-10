<?php

declare(strict_types=1);

namespace Tenant\Application\RegisterTenant;

final readonly class RegisterTenantCommand
{
    public function __construct(
        public string $ownerName,
        public string $ownerEmail,
        public string $organizationName,
        public string $subscriptionPlanId,
    ) {}
}
