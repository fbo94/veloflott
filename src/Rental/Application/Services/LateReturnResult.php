<?php

declare(strict_types=1);

namespace Rental\Application\Services;

final readonly class LateReturnResult
{
    public function __construct(
        public bool $isLate,
        public int $minutesLate,
        public int $hoursLate,
        public int $daysLate,
        public float $feeAmount,
        public int $toleranceMinutes,
        public bool $withinTolerance,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'is_late' => $this->isLate,
            'minutes_late' => $this->minutesLate,
            'hours_late' => $this->hoursLate,
            'days_late' => $this->daysLate,
            'fee_amount' => $this->feeAmount,
            'tolerance_minutes' => $this->toleranceMinutes,
            'within_tolerance' => $this->withinTolerance,
        ];
    }
}
