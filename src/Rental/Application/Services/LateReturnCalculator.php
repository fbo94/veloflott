<?php

declare(strict_types=1);

namespace Rental\Application\Services;

use DateTimeImmutable;
use Rental\Domain\Repository\RentalSettingsRepositoryInterface;

final class LateReturnCalculator
{
    public function __construct(
        private RentalSettingsRepositoryInterface $settingsRepository,
    ) {
    }

    /**
     * Calculate the late return fees
     */
    public function calculate(
        DateTimeImmutable $expectedReturnDate,
        DateTimeImmutable $actualReturnDate,
        ?string $tenantId = null,
        ?string $siteId = null,
    ): LateReturnResult {
        // Get effective settings
        $settings = $this->settingsRepository->getEffectiveSettings($tenantId, $siteId);

        // If returned on time or early, no fee
        if ($actualReturnDate <= $expectedReturnDate) {
            return new LateReturnResult(
                isLate: false,
                minutesLate: 0,
                hoursLate: 0,
                daysLate: 0,
                feeAmount: 0.0,
                toleranceMinutes: $settings->lateToleranceMinutes(),
                withinTolerance: true,
            );
        }

        // Calculate minutes late
        $diff = $expectedReturnDate->diff($actualReturnDate);
        $minutesLate = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;

        // Check tolerance
        if ($minutesLate <= $settings->lateToleranceMinutes()) {
            return new LateReturnResult(
                isLate: true,
                minutesLate: $minutesLate,
                hoursLate: 0,
                daysLate: 0,
                feeAmount: 0.0,
                toleranceMinutes: $settings->lateToleranceMinutes(),
                withinTolerance: true,
            );
        }

        // Calculate billable time (after tolerance)
        $billableMinutes = $minutesLate - $settings->lateToleranceMinutes();
        $hoursLate = (int) ceil($billableMinutes / 60);
        $daysLate = (int) ceil($hoursLate / 24);

        // Calculate fee: hourly if < 24 hours, daily otherwise
        $feeAmount = $hoursLate < 24
            ? $hoursLate * $settings->hourlyLateRate()
            : $daysLate * $settings->dailyLateRate();

        return new LateReturnResult(
            isLate: true,
            minutesLate: $minutesLate,
            hoursLate: $hoursLate,
            daysLate: $daysLate,
            feeAmount: $feeAmount,
            toleranceMinutes: $settings->lateToleranceMinutes(),
            withinTolerance: false,
        );
    }
}
