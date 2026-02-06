<?php

declare(strict_types=1);

namespace Customer\Application\SearchCustomers;

final readonly class SearchCustomersQuery
{
    public function __construct(
        public ?string $search = null,
    ) {}
}
