<?php

declare(strict_types=1);

namespace Rental\Domain;

use DateTimeImmutable;

final class DepositRetentionConfig
{
    private function __construct(
        private DepositRetentionConfigId $id,
        private ?string $bikeId,
        private ?string $pricingClassId,
        private ?string $categoryId,
        private float $minorDamageAmount,
        private float $majorDamageAmount,
        private float $totalLossAmount,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {
    }

    public static function createForBike(
        DepositRetentionConfigId $id,
        string $bikeId,
        float $minorDamageAmount,
        float $majorDamageAmount,
        float $totalLossAmount,
    ): self {
        self::validateAmounts($minorDamageAmount, $majorDamageAmount, $totalLossAmount);

        return new self(
            id: $id,
            bikeId: $bikeId,
            pricingClassId: null,
            categoryId: null,
            minorDamageAmount: $minorDamageAmount,
            majorDamageAmount: $majorDamageAmount,
            totalLossAmount: $totalLossAmount,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );
    }

    public static function createForPricingClass(
        DepositRetentionConfigId $id,
        string $pricingClassId,
        float $minorDamageAmount,
        float $majorDamageAmount,
        float $totalLossAmount,
    ): self {
        self::validateAmounts($minorDamageAmount, $majorDamageAmount, $totalLossAmount);

        return new self(
            id: $id,
            bikeId: null,
            pricingClassId: $pricingClassId,
            categoryId: null,
            minorDamageAmount: $minorDamageAmount,
            majorDamageAmount: $majorDamageAmount,
            totalLossAmount: $totalLossAmount,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );
    }

    public static function createForCategory(
        DepositRetentionConfigId $id,
        string $categoryId,
        float $minorDamageAmount,
        float $majorDamageAmount,
        float $totalLossAmount,
    ): self {
        self::validateAmounts($minorDamageAmount, $majorDamageAmount, $totalLossAmount);

        return new self(
            id: $id,
            bikeId: null,
            pricingClassId: null,
            categoryId: $categoryId,
            minorDamageAmount: $minorDamageAmount,
            majorDamageAmount: $majorDamageAmount,
            totalLossAmount: $totalLossAmount,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        DepositRetentionConfigId $id,
        ?string $bikeId,
        ?string $pricingClassId,
        ?string $categoryId,
        float $minorDamageAmount,
        float $majorDamageAmount,
        float $totalLossAmount,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            id: $id,
            bikeId: $bikeId,
            pricingClassId: $pricingClassId,
            categoryId: $categoryId,
            minorDamageAmount: $minorDamageAmount,
            majorDamageAmount: $majorDamageAmount,
            totalLossAmount: $totalLossAmount,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    private static function validateAmounts(
        float $minorDamageAmount,
        float $majorDamageAmount,
        float $totalLossAmount,
    ): void {
        if ($minorDamageAmount < 0 || $majorDamageAmount < 0 || $totalLossAmount < 0) {
            throw new \DomainException('Damage amounts cannot be negative');
        }

        if ($minorDamageAmount > $majorDamageAmount) {
            throw new \DomainException('Minor damage amount cannot exceed major damage amount');
        }

        if ($majorDamageAmount > $totalLossAmount) {
            throw new \DomainException('Major damage amount cannot exceed total loss amount');
        }
    }

    public function update(
        float $minorDamageAmount,
        float $majorDamageAmount,
        float $totalLossAmount,
    ): void {
        self::validateAmounts($minorDamageAmount, $majorDamageAmount, $totalLossAmount);

        $this->minorDamageAmount = $minorDamageAmount;
        $this->majorDamageAmount = $majorDamageAmount;
        $this->totalLossAmount = $totalLossAmount;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getRetentionAmount(DamageLevel $level): float
    {
        return match ($level) {
            DamageLevel::NONE => 0.0,
            DamageLevel::MINOR => $this->minorDamageAmount,
            DamageLevel::MAJOR => $this->majorDamageAmount,
            DamageLevel::TOTAL_LOSS => $this->totalLossAmount,
        };
    }

    public function id(): DepositRetentionConfigId
    {
        return $this->id;
    }

    public function bikeId(): ?string
    {
        return $this->bikeId;
    }

    public function pricingClassId(): ?string
    {
        return $this->pricingClassId;
    }

    public function categoryId(): ?string
    {
        return $this->categoryId;
    }

    public function minorDamageAmount(): float
    {
        return $this->minorDamageAmount;
    }

    public function majorDamageAmount(): float
    {
        return $this->majorDamageAmount;
    }

    public function totalLossAmount(): float
    {
        return $this->totalLossAmount;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isForBike(): bool
    {
        return $this->bikeId !== null;
    }

    public function isForPricingClass(): bool
    {
        return $this->pricingClassId !== null;
    }

    public function isForCategory(): bool
    {
        return $this->categoryId !== null;
    }
}
