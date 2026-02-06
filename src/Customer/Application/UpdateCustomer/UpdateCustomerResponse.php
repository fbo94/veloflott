<?php

declare(strict_types=1);

namespace Customer\Application\UpdateCustomer;

final readonly class UpdateCustomerResponse
{
    public function __construct(
        public string $customerId,
        public string $message,
    ) {}

    public function toArray(): array
    {
        return [
            'customer_id' => $this->customerId,
            'message' => $this->message,
        ];
    }
}
