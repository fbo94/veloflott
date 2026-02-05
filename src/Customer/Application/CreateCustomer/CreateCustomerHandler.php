<?php

declare(strict_types=1);

namespace Customer\Application\CreateCustomer;

use Customer\Domain\Customer;
use Customer\Domain\CustomerRepositoryInterface;
use Illuminate\Support\Str;

final class CreateCustomerHandler
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers,
    ) {
    }

    public function handle(CreateCustomerCommand $command): CreateCustomerResponse
    {
        // Vérifier l'unicité de l'email si fourni
        if ($command->email !== null) {
            $existing = $this->customers->findByEmail($command->email);
            if ($existing !== null) {
                throw new CustomerEmailAlreadyExistsException($command->email);
            }
        }

        $customer = new Customer(
            id: Str::uuid()->toString(),
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
            isRisky: false,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->customers->save($customer);

        return CreateCustomerResponse::fromCustomer($customer);
    }
}
