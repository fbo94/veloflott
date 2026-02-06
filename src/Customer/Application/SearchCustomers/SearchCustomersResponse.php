<?php

declare(strict_types=1);

namespace Customer\Application\SearchCustomers;

final readonly class SearchCustomersResponse
{
    /**
     * @param  CustomerDto[]  $customers
     */
    public function __construct(
        public array $customers,
    ) {}

    public function toArray(): array
    {
        return [
            'data' => array_map(
                fn (CustomerDto $customer) => $customer->toArray(),
                $this->customers
            ),
        ];
    }
}
