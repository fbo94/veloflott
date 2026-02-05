<?php

declare(strict_types=1);

namespace Rental\Application\CancelRental;

final readonly class CancelRentalResponse
{
    public function __construct(
        public string $rentalId,
        public string $status,
        public string $cancellationReason,
        public string $depositStatus,
        public string $message,
    ) {}

    public function toArray(): array
    {
        return [
            'rental_id' => $this->rentalId,
            'status' => $this->status,
            'cancellation_reason' => $this->cancellationReason,
            'deposit_status' => $this->depositStatus,
            'message' => $this->message,
        ];
    }
}
