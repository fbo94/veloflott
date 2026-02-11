<?php

declare(strict_types=1);

namespace Tenant\Application\ListTenants;

final readonly class ListTenantsQuery
{
    public function __construct(
        public ?string $status = null,
        public ?string $search = null,
    ) {
    }
}
