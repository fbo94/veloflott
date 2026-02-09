<?php

declare(strict_types=1);

namespace Rental\Application\Services;

use Rental\Domain\DamageLevel;
use Rental\Domain\Repository\DepositRetentionConfigRepositoryInterface;

final class DepositRetentionCalculator
{
    public function __construct(
        private DepositRetentionConfigRepositoryInterface $configRepository,
    ) {
    }

    /**
     * Calculate the deposit retention amount based on damage level
     * Hierarchy: Bike > PricingClass > Category > FullDeposit
     */
    public function calculate(
        string $bikeId,
        ?string $pricingClassId,
        string $categoryId,
        DamageLevel $damageLevel,
        float $depositAmount,
    ): DepositRetentionResult {
        if ($damageLevel === DamageLevel::NONE) {
            return new DepositRetentionResult(
                retentionAmount: 0.0,
                refundAmount: $depositAmount,
                damageLevel: $damageLevel,
                configSource: null,
            );
        }

        // 1. Try to find config for the specific bike
        $config = $this->configRepository->findByBikeId($bikeId);
        if ($config !== null) {
            return $this->buildResult($config->getRetentionAmount($damageLevel), $depositAmount, $damageLevel, 'bike');
        }

        // 2. Try to find config for the pricing class
        if ($pricingClassId !== null) {
            $config = $this->configRepository->findByPricingClassId($pricingClassId);
            if ($config !== null) {
                return $this->buildResult($config->getRetentionAmount($damageLevel), $depositAmount, $damageLevel, 'pricing_class');
            }
        }

        // 3. Try to find config for the category
        $config = $this->configRepository->findByCategoryId($categoryId);
        if ($config !== null) {
            return $this->buildResult($config->getRetentionAmount($damageLevel), $depositAmount, $damageLevel, 'category');
        }

        // 4. No config found: retain full deposit for damage cases
        return new DepositRetentionResult(
            retentionAmount: $depositAmount,
            refundAmount: 0.0,
            damageLevel: $damageLevel,
            configSource: 'default_full_retention',
        );
    }

    private function buildResult(
        float $retentionAmount,
        float $depositAmount,
        DamageLevel $damageLevel,
        string $configSource,
    ): DepositRetentionResult {
        // Retention cannot exceed deposit
        $effectiveRetention = min($retentionAmount, $depositAmount);

        return new DepositRetentionResult(
            retentionAmount: $effectiveRetention,
            refundAmount: $depositAmount - $effectiveRetention,
            damageLevel: $damageLevel,
            configSource: $configSource,
        );
    }
}
