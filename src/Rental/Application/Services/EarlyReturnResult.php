<?php

declare(strict_types=1);

namespace Rental\Application\Services;

use Rental\Domain\EarlyReturnFeeType;

final readonly class EarlyReturnResult
{
    public function __construct(
        public int $unusedDays,
        public float $unusedAmount,
        public float $feeAmount,
        public float $refundAmount,
        public EarlyReturnFeeType $feeType,
        public bool $isEnabled,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'unused_days' => $this->unusedDays,
            'unused_amount' => $this->unusedAmount,
            'fee_amount' => $this->feeAmount,
            'refund_amount' => $this->refundAmount,
            'fee_type' => $this->feeType->value,
            'fee_type_label' => $this->feeType->label(),
            'is_enabled' => $this->isEnabled,
        ];
    }
}
