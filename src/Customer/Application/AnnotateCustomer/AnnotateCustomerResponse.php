<?php

declare(strict_types=1);

namespace Customer\Application\AnnotateCustomer;

final readonly class AnnotateCustomerResponse
{
    public function __construct(
        public string $customerId,
        public ?string $annotation,
        public bool $isRiskyCustomer,
        public string $message,
    ) {
    }

    /**
     * @return array{customer_id: string, annotation: string|null, is_risky_customer: bool, message: string}
     */
    public function toArray(): array
    {
        return [
            'customer_id' => $this->customerId,
            'annotation' => $this->annotation,
            'is_risky_customer' => $this->isRiskyCustomer,
            'message' => $this->message,
        ];
    }
}
