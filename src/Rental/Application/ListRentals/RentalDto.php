<?php

declare(strict_types=1);

namespace Rental\Application\ListRentals;

use Rental\Domain\Rental;

final readonly class RentalDto
{
    /**
     * @param  string[]  $bikes
     */
    public function __construct(
        public string $id,
        public string $customerId,
        public string $customerName,
        public string $startDate,
        public string $expectedReturnDate,
        public ?string $actualReturnDate,
        public array $bikes,
        public string $status,
        public string $duration,
        public float $totalAmount,
        public float $depositAmount,
        public string $depositStatus,
        public ?float $depositRetained,
        public string $createdAt,
    ) {
    }

    public static function fromRental(Rental $rental, string $customerName, array $bikeDetails): self
    {
        return new self(
            id: $rental->id(),
            customerId: $rental->customerId(),
            customerName: $customerName,
            startDate: $rental->startDate()->format('Y-m-d H:i'),
            expectedReturnDate: $rental->expectedReturnDate()->format('Y-m-d H:i'),
            actualReturnDate: $rental->actualReturnDate()?->format('Y-m-d H:i'),
            bikes: $bikeDetails,
            status: $rental->status()->value,
            duration: $rental->duration()->value,
            totalAmount: $rental->totalAmount(),
            depositAmount: $rental->depositAmount(),
            depositStatus: $rental->depositStatus()->value,
            depositRetained: $rental->depositRetained(),
            createdAt: $rental->createdAt()->format('Y-m-d H:i'),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customerId,
            'customer_name' => $this->customerName,
            'start_date' => $this->startDate,
            'expected_return_date' => $this->expectedReturnDate,
            'actual_return_date' => $this->actualReturnDate,
            'bikes' => $this->bikes,
            'status' => $this->status,
            'duration' => $this->duration,
            'total_amount' => $this->totalAmount,
            'deposit_amount' => $this->depositAmount,
            'deposit_status' => $this->depositStatus,
            'deposit_retained' => $this->depositRetained,
            'created_at' => $this->createdAt,
        ];
    }
}
