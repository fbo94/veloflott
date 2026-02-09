<?php

declare(strict_types=1);

namespace Rental\Domain;

use DateTimeImmutable;

final class RentalSettings
{
    private function __construct(
        private RentalSettingsId $id,
        private ?string $tenantId,
        private ?string $siteId,
        private int $lateToleranceMinutes,
        private float $hourlyLateRate,
        private float $dailyLateRate,
        private bool $earlyReturnEnabled,
        private EarlyReturnFeeType $earlyReturnFeeType,
        private ?float $earlyReturnFeePercentage,
        private ?float $earlyReturnFeeFixed,
        private int $maxRentalDurationDays,
        private int $minReservationHoursAhead,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {
    }

    public static function create(
        RentalSettingsId $id,
        ?string $tenantId,
        ?string $siteId,
        int $lateToleranceMinutes = 30,
        float $hourlyLateRate = 10.00,
        float $dailyLateRate = 50.00,
        bool $earlyReturnEnabled = true,
        EarlyReturnFeeType $earlyReturnFeeType = EarlyReturnFeeType::PERCENTAGE,
        ?float $earlyReturnFeePercentage = 30.00,
        ?float $earlyReturnFeeFixed = 25.00,
        int $maxRentalDurationDays = 30,
        int $minReservationHoursAhead = 2,
    ): self {
        self::validateScope($tenantId, $siteId);

        return new self(
            id: $id,
            tenantId: $tenantId,
            siteId: $siteId,
            lateToleranceMinutes: $lateToleranceMinutes,
            hourlyLateRate: $hourlyLateRate,
            dailyLateRate: $dailyLateRate,
            earlyReturnEnabled: $earlyReturnEnabled,
            earlyReturnFeeType: $earlyReturnFeeType,
            earlyReturnFeePercentage: $earlyReturnFeePercentage,
            earlyReturnFeeFixed: $earlyReturnFeeFixed,
            maxRentalDurationDays: $maxRentalDurationDays,
            minReservationHoursAhead: $minReservationHoursAhead,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        RentalSettingsId $id,
        ?string $tenantId,
        ?string $siteId,
        int $lateToleranceMinutes,
        float $hourlyLateRate,
        float $dailyLateRate,
        bool $earlyReturnEnabled,
        EarlyReturnFeeType $earlyReturnFeeType,
        ?float $earlyReturnFeePercentage,
        ?float $earlyReturnFeeFixed,
        int $maxRentalDurationDays,
        int $minReservationHoursAhead,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            id: $id,
            tenantId: $tenantId,
            siteId: $siteId,
            lateToleranceMinutes: $lateToleranceMinutes,
            hourlyLateRate: $hourlyLateRate,
            dailyLateRate: $dailyLateRate,
            earlyReturnEnabled: $earlyReturnEnabled,
            earlyReturnFeeType: $earlyReturnFeeType,
            earlyReturnFeePercentage: $earlyReturnFeePercentage,
            earlyReturnFeeFixed: $earlyReturnFeeFixed,
            maxRentalDurationDays: $maxRentalDurationDays,
            minReservationHoursAhead: $minReservationHoursAhead,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    private static function validateScope(?string $tenantId, ?string $siteId): void
    {
        // Si siteId est défini, tenantId doit l'être aussi
        if ($siteId !== null && $tenantId === null) {
            throw new \DomainException('Site cannot be defined without a tenant');
        }
    }

    public function update(
        int $lateToleranceMinutes,
        float $hourlyLateRate,
        float $dailyLateRate,
        bool $earlyReturnEnabled,
        EarlyReturnFeeType $earlyReturnFeeType,
        ?float $earlyReturnFeePercentage,
        ?float $earlyReturnFeeFixed,
        int $maxRentalDurationDays,
        int $minReservationHoursAhead,
    ): void {
        $this->lateToleranceMinutes = $lateToleranceMinutes;
        $this->hourlyLateRate = $hourlyLateRate;
        $this->dailyLateRate = $dailyLateRate;
        $this->earlyReturnEnabled = $earlyReturnEnabled;
        $this->earlyReturnFeeType = $earlyReturnFeeType;
        $this->earlyReturnFeePercentage = $earlyReturnFeePercentage;
        $this->earlyReturnFeeFixed = $earlyReturnFeeFixed;
        $this->maxRentalDurationDays = $maxRentalDurationDays;
        $this->minReservationHoursAhead = $minReservationHoursAhead;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): RentalSettingsId
    {
        return $this->id;
    }

    public function tenantId(): ?string
    {
        return $this->tenantId;
    }

    public function siteId(): ?string
    {
        return $this->siteId;
    }

    public function lateToleranceMinutes(): int
    {
        return $this->lateToleranceMinutes;
    }

    public function hourlyLateRate(): float
    {
        return $this->hourlyLateRate;
    }

    public function dailyLateRate(): float
    {
        return $this->dailyLateRate;
    }

    public function earlyReturnEnabled(): bool
    {
        return $this->earlyReturnEnabled;
    }

    public function earlyReturnFeeType(): EarlyReturnFeeType
    {
        return $this->earlyReturnFeeType;
    }

    public function earlyReturnFeePercentage(): ?float
    {
        return $this->earlyReturnFeePercentage;
    }

    public function earlyReturnFeeFixed(): ?float
    {
        return $this->earlyReturnFeeFixed;
    }

    public function maxRentalDurationDays(): int
    {
        return $this->maxRentalDurationDays;
    }

    public function minReservationHoursAhead(): int
    {
        return $this->minReservationHoursAhead;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isAppDefault(): bool
    {
        return $this->tenantId === null && $this->siteId === null;
    }

    public function isTenantDefault(): bool
    {
        return $this->tenantId !== null && $this->siteId === null;
    }

    public function isSiteSpecific(): bool
    {
        return $this->tenantId !== null && $this->siteId !== null;
    }
}
