<?php

declare(strict_types=1);

namespace Rental\Application\GetBikeRentals;

use Rental\Domain\Rental;

final readonly class BikeRentalDto
{
    public function __construct(
        public string $id,
        public string $customerId,
        public string $startDate,
        public string $expectedReturnDate,
        public ?string $actualReturnDate,
        public string $status,
        public float $totalAmount,
        public float $depositAmount,
        public string $depositStatus,
        public ?float $depositRetained,
        public ?string $cancellationReason,
    ) {}

    public static function fromDomain(Rental $rental): self
    {
        return new self(
            id: $rental->id(),
            customerId: $rental->customerId(),
            startDate: $rental->startDate()->format('Y-m-d H:i:s'),
            expectedReturnDate: $rental->expectedReturnDate()->format('Y-m-d H:i:s'),
            actualReturnDate: $rental->actualReturnDate()?->format('Y-m-d H:i:s'),
            status: $rental->status()->value,
            totalAmount: $rental->totalAmount(),
            depositAmount: $rental->depositAmount(),
            depositStatus: $rental->depositStatus()->value,
            depositRetained: $rental->depositRetained(),
            cancellationReason: $rental->cancellationReason(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customerId,
            'start_date' => $this->startDate,
            'expected_return_date' => $this->expectedReturnDate,
            'actual_return_date' => $this->actualReturnDate,
            'status' => $this->status,
            'total_amount' => $this->totalAmount,
            'deposit_amount' => $this->depositAmount,
            'deposit_status' => $this->depositStatus,
            'deposit_retained' => $this->depositRetained,
            'cancellation_reason' => $this->cancellationReason,
        ];
    }
}
