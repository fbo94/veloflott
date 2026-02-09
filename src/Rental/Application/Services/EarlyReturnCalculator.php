<?php

declare(strict_types=1);

namespace Rental\Application\Services;

use DateTimeImmutable;
use Rental\Domain\EarlyReturnFeeType;
use Rental\Domain\RentalSettings;
use Rental\Domain\Repository\RentalSettingsRepositoryInterface;

final class EarlyReturnCalculator
{
    public function __construct(
        private RentalSettingsRepositoryInterface $settingsRepository,
    ) {
    }

    /**
     * Calculate the early return fees and refund
     */
    public function calculate(
        DateTimeImmutable $startDate,
        DateTimeImmutable $expectedReturnDate,
        DateTimeImmutable $actualReturnDate,
        float $totalAmount,
        ?string $tenantId = null,
        ?string $siteId = null,
    ): EarlyReturnResult {
        // Get effective settings
        $settings = $this->settingsRepository->getEffectiveSettings($tenantId, $siteId);

        // Calculate unused days
        $totalDays = max(1, (int) $startDate->diff($expectedReturnDate)->days);
        $usedDays = max(0, (int) $startDate->diff($actualReturnDate)->days);
        $unusedDays = max(0, $totalDays - $usedDays);

        // If no unused days, no refund
        if ($unusedDays <= 0) {
            return new EarlyReturnResult(
                unusedDays: 0,
                unusedAmount: 0.0,
                feeAmount: 0.0,
                refundAmount: 0.0,
                feeType: $settings->earlyReturnFeeType(),
                isEnabled: $settings->earlyReturnEnabled(),
            );
        }

        // Calculate unused amount (prorated)
        $dailyRate = $totalAmount / $totalDays;
        $unusedAmount = $dailyRate * $unusedDays;

        // If early return is disabled, full refund
        if (!$settings->earlyReturnEnabled()) {
            return new EarlyReturnResult(
                unusedDays: $unusedDays,
                unusedAmount: $unusedAmount,
                feeAmount: 0.0,
                refundAmount: $unusedAmount,
                feeType: EarlyReturnFeeType::NONE,
                isEnabled: false,
            );
        }

        // Calculate fee based on type
        $feeAmount = $this->calculateFee($settings, $unusedAmount);

        // Fee cannot exceed unused amount
        $effectiveFee = min($feeAmount, $unusedAmount);

        return new EarlyReturnResult(
            unusedDays: $unusedDays,
            unusedAmount: $unusedAmount,
            feeAmount: $effectiveFee,
            refundAmount: $unusedAmount - $effectiveFee,
            feeType: $settings->earlyReturnFeeType(),
            isEnabled: $settings->earlyReturnEnabled(),
        );
    }

    private function calculateFee(RentalSettings $settings, float $unusedAmount): float
    {
        return match ($settings->earlyReturnFeeType()) {
            EarlyReturnFeeType::PERCENTAGE => $unusedAmount * ($settings->earlyReturnFeePercentage() ?? 0) / 100,
            EarlyReturnFeeType::FIXED => $settings->earlyReturnFeeFixed() ?? 0,
            EarlyReturnFeeType::NONE => 0.0,
        };
    }
}
