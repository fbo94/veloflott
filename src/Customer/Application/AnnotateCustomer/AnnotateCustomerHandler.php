<?php

declare(strict_types=1);

namespace Customer\Application\AnnotateCustomer;

use Customer\Domain\CustomerRepositoryInterface;

final readonly class AnnotateCustomerHandler
{
    public function __construct(
        private CustomerRepositoryInterface $customerRepository,
    ) {
    }

    public function handle(AnnotateCustomerCommand $command): AnnotateCustomerResponse
    {
        $customer = $this->customerRepository->findById($command->customerId);

        if ($customer === null) {
            throw new \DomainException("Customer with ID {$command->customerId} not found");
        }

        $customer->annotate(
            annotation: $command->annotation,
            isRiskyCustomer: $command->isRiskyCustomer
        );

        $this->customerRepository->save($customer);

        return new AnnotateCustomerResponse(
            customerId: $customer->id(),
            annotation: $customer->notes(),
            isRiskyCustomer: $customer->isRisky(),
            message: 'Customer annotated successfully'
        );
    }
}
