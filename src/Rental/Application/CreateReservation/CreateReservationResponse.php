<?php

declare(strict_types=1);

namespace Rental\Application\CreateReservation;

use Customer\Domain\Customer;
use Rental\Domain\Rental;
use Rental\Domain\RentalStatus;

final readonly class CreateReservationResponse
{
    public function __construct(
        public string $rentalId,
        public string $customerId,
        public string $customerName,
        public string $status,
        public string $statusLabel,
        public string $startDate,
        public string $expectedReturnDate,
        public float $totalAmount,
        public float $depositAmount,
        public int $bikesCount,
        public string $message,
    ) {
    }

    public static function fromRental(Rental $rental, Customer $customer, RentalStatus $status): self
    {
        $message = $status === RentalStatus::RESERVED
            ? 'Reservation created successfully. Bikes are reserved for the specified period.'
            : 'Rental created successfully. Ready for check-in.';

        return new self(
            rentalId: $rental->id(),
            customerId: $customer->id(),
            customerName: $customer->firstName() . ' ' . $customer->lastName(),
            status: $status->value,
            statusLabel: $status->label(),
            startDate: $rental->startDate()->format('Y-m-d H:i:s'),
            expectedReturnDate: $rental->expectedReturnDate()->format('Y-m-d H:i:s'),
            totalAmount: $rental->totalWithTax(),
            depositAmount: $rental->depositAmount(),
            bikesCount: count($rental->items()),
            message: $message,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'rental_id' => $this->rentalId,
            'customer_id' => $this->customerId,
            'customer_name' => $this->customerName,
            'status' => $this->status,
            'status_label' => $this->statusLabel,
            'start_date' => $this->startDate,
            'expected_return_date' => $this->expectedReturnDate,
            'total_amount' => $this->totalAmount,
            'deposit_amount' => $this->depositAmount,
            'bikes_count' => $this->bikesCount,
            'message' => $this->message,
        ];
    }
}
