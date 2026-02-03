<?php

declare(strict_types=1);

namespace Customer\Application\UpdateCustomer;

use Customer\Application\CreateCustomer\CustomerEmailAlreadyExistsException;
use Customer\Domain\CustomerRepositoryInterface;

final readonly class UpdateCustomerHandler
{
    public function __construct(
        private CustomerRepositoryInterface $customers,
    ) {}

    public function handle(UpdateCustomerCommand $command): UpdateCustomerResponse
    {
        // Récupérer le client
        $customer = $this->customers->findById($command->customerId);
        if ($customer === null) {
            throw new \DomainException("Customer with ID {$command->customerId} not found");
        }

        // Vérifier l'unicité de l'email si modifié
        if ($command->email !== null && $command->email !== $customer->email()) {
            $existing = $this->customers->findByEmail($command->email);
            if ($existing !== null && $existing->id() !== $customer->id()) {
                throw new CustomerEmailAlreadyExistsException($command->email);
            }
        }

        // Mettre à jour le client
        $customer->update(
            firstName: $command->firstName,
            lastName: $command->lastName,
            email: $command->email,
            phone: $command->phone,
            identityDocumentType: $command->identityDocumentType,
            identityDocumentNumber: $command->identityDocumentNumber,
            height: $command->height,
            weight: $command->weight,
            address: $command->address,
            notes: $command->notes,
        );

        $this->customers->save($customer);

        return new UpdateCustomerResponse(
            customerId: $customer->id(),
            message: 'Customer updated successfully',
        );
    }
}
