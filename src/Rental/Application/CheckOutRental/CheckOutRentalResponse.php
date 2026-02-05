<?php

declare(strict_types=1);

namespace Rental\Application\CheckOutRental;

use Rental\Domain\Rental;

final readonly class CheckOutRentalResponse
{
    public function __construct(
        public string $id,
        public string $status,
        public string $actualReturnDate,
        public float $lateFee,
        public float $finalAmount,
        public string $depositStatus,
        public float $depositRetained,
        public float $depositReturned,
    ) {
    }

    public static function fromRental(Rental $rental, float $lateFee): self
    {
        return new self(
            id: $rental->id(),
            status: $rental->status()->value,
            actualReturnDate: $rental->actualReturnDate()->format('Y-m-d H:i:s'),
            lateFee: $lateFee,
            finalAmount: $rental->totalAmount(),
            depositStatus: $rental->depositStatus()->value,
            depositRetained: $rental->depositRetained() ?? 0.0,
            depositReturned: $rental->depositAmount() - ($rental->depositRetained() ?? 0.0),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'actual_return_date' => $this->actualReturnDate,
            'late_fee' => $this->lateFee,
            'final_amount' => $this->finalAmount,
            'deposit' => [
                'status' => $this->depositStatus,
                'retained' => $this->depositRetained,
                'returned' => $this->depositReturned,
            ],
        ];
    }
}
