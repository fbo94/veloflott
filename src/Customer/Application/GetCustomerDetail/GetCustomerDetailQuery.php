<?php

declare(strict_types=1);

namespace Customer\Application\GetCustomerDetail;

final readonly class GetCustomerDetailQuery
{
    public function __construct(
        public string $customerId,
    ) {}
}
