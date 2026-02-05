<?php

declare(strict_types=1);

namespace Customer\Application\SearchCustomers;

use Customer\Domain\CustomerRepositoryInterface;

final class SearchCustomersHandler
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers,
    ) {
    }

    public function handle(SearchCustomersQuery $query): SearchCustomersResponse
    {
        $customers = $query->search !== null && $query->search !== ''
            ? $this->customers->search($query->search)
            : $this->customers->findAll();

        $customerDtos = array_map(
            fn ($customer) => CustomerDto::fromCustomer($customer),
            $customers
        );

        return new SearchCustomersResponse($customerDtos);
    }
}
