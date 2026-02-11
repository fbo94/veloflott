<?php

declare(strict_types=1);

namespace Rental\Application\EarlyReturn;

final readonly class EarlyReturnResponse
{
    public function __construct(
        public string $rentalId,
        public string $status,
        public string $actualReturnDate,
        public float $originalAmount,
        public int $unusedDays,
        public float $unusedAmount,
        public float $earlyReturnFee,
        public float $refundAmount,
        public float $depositAmount,
        public float $depositRetained,
        public float $depositRefunded,
        public string $depositStatus,
        public string $message,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'rental_id' => $this->rentalId,
            'status' => $this->status,
            'actual_return_date' => $this->actualReturnDate,
            'original_amount' => $this->originalAmount,
            'unused_days' => $this->unusedDays,
            'unused_amount' => $this->unusedAmount,
            'early_return_fee' => $this->earlyReturnFee,
            'refund_amount' => $this->refundAmount,
            'deposit_amount' => $this->depositAmount,
            'deposit_retained' => $this->depositRetained,
            'deposit_refunded' => $this->depositRefunded,
            'deposit_status' => $this->depositStatus,
            'message' => $this->message,
        ];
    }
}
