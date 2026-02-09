<?php

declare(strict_types=1);

namespace Customer\Application\GetCustomerDetail;

final readonly class CustomerDetailDto
{
    /**
     * @param  array<int, array<string, mixed>>  $rentalHistory
     */
    public function __construct(
        public string $id,
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
        public bool $isRisky,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
        public array $rentalHistory,
        public int $totalRentals,
        public float $totalSpent,
    ) {
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
            'identity_document_type' => $this->identityDocumentType,
            'identity_document_number' => $this->identityDocumentNumber,
            'height' => $this->height,
            'weight' => $this->weight,
            'address' => $this->address,
            'notes' => $this->notes,
            'is_risky' => $this->isRisky,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'rental_history' => $this->rentalHistory,
            'statistics' => [
                'total_rentals' => $this->totalRentals,
                'total_spent' => $this->totalSpent,
            ],
        ];
    }
}
