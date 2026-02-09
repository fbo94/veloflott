<?php

declare(strict_types=1);

namespace Customer\Application\AnnotateCustomer;

final readonly class AnnotateCustomerCommand
{
    public function __construct(
        public string $customerId,
        public ?string $annotation,
        public bool $isRiskyCustomer,
    ) {
    }
}
