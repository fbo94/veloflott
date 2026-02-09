<?php

declare(strict_types=1);

namespace Rental\Application\CreateRental;

use Customer\Domain\Customer;
use Rental\Domain\Rental;

final readonly class CreateRentalResponse
{
    public function __construct(
        public string $id,
        public string $customerId,
        public string $customerName,
        public string $startDate,
        public string $expectedReturnDate,
        public string $duration,
        public float $depositAmount,
        public float $totalAmount,
        public string $status,
        public int $bikesCount,
        public int $equipmentsCount,
    ) {
    }

    public static function fromRental(Rental $rental, Customer $customer): self
    {
        return new self(
            id: $rental->id(),
            customerId: $rental->customerId(),
            customerName: $customer->fullName(),
            startDate: $rental->startDate()->format('Y-m-d H:i:s'),
            expectedReturnDate: $rental->expectedReturnDate()->format('Y-m-d H:i:s'),
            duration: $rental->duration()->value,
            depositAmount: $rental->depositAmount(),
            totalAmount: $rental->totalAmount(),
            status: $rental->status()->value,
            bikesCount: count($rental->items()),
            equipmentsCount: count($rental->equipments()),
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
            'duration' => $this->duration,
            'deposit_amount' => $this->depositAmount,
            'total_amount' => $this->totalAmount,
            'status' => $this->status,
            'bikes_count' => $this->bikesCount,
            'equipments_count' => $this->equipmentsCount,
        ];
    }
}
