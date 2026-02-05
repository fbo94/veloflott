<?php

declare(strict_types=1);

namespace Customer\Application\CreateCustomer;

use Customer\Domain\Customer;

final readonly class CreateCustomerResponse
{
    public function __construct(
        public string $id,
        public string $firstName,
        public string $lastName,
        public ?string $email,
        public ?string $phone,
    ) {
    }

    public static function fromCustomer(Customer $customer): self
    {
        return new self(
            id: $customer->id(),
            firstName: $customer->firstName(),
            lastName: $customer->lastName(),
            email: $customer->email(),
            phone: $customer->phone(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'full_name' => "{$this->firstName} {$this->lastName}",
            'email' => $this->email,
            'phone' => $this->phone,
        ];
    }
}
