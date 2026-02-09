<?php

declare(strict_types=1);

namespace Customer\Application\UpdateCustomer;

final readonly class UpdateCustomerCommand
{
    /**
     * @param  array<int, string>  $photos
     */
    public function __construct(
        public string $customerId,
        public string $firstName,
        public string $lastName,
        public ?string $email,
        public ?string $phone,
        public ?string $identityDocumentType,
        public ?string $identityDocumentNumber,
        public ?int $height,
        public ?int $weight,
        public ?string $address,
        public ?string $notes,
        public array $photos = [],
    ) {
    }
}
