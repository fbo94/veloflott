<?php

declare(strict_types=1);

namespace Customer\Application\ToggleRiskyFlag;

use Customer\Domain\CustomerRepositoryInterface;

final readonly class ToggleRiskyFlagHandler
{
    public function __construct(
        private CustomerRepositoryInterface $customers,
    ) {
    }

    public function handle(ToggleRiskyFlagCommand $command): ToggleRiskyFlagResponse
    {
        $customer = $this->customers->findById($command->customerId);
        if ($customer === null) {
            throw new \DomainException("Customer with ID {$command->customerId} not found");
        }

        // Toggle le flag risque
        if ($customer->isRisky()) {
            $customer->unmarkAsRisky();
            $message = 'Customer unmarked as risky';
        } else {
            $customer->markAsRisky();
            $message = 'Customer marked as risky';
        }

        $this->customers->save($customer);

        return new ToggleRiskyFlagResponse(
            customerId: $customer->id(),
            isRisky: $customer->isRisky(),
            message: $message,
        );
    }
}
