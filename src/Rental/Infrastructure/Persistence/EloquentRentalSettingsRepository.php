<?php

declare(strict_types=1);

namespace Rental\Infrastructure\Persistence;

use Rental\Domain\EarlyReturnFeeType;
use Rental\Domain\RentalSettings;
use Rental\Domain\RentalSettingsId;
use Rental\Domain\Repository\RentalSettingsRepositoryInterface;
use Rental\Infrastructure\Persistence\Models\RentalSettingsEloquentModel;

final class EloquentRentalSettingsRepository implements RentalSettingsRepositoryInterface
{
    public function save(RentalSettings $settings): void
    {
        RentalSettingsEloquentModel::updateOrCreate(
            ['id' => $settings->id()->value()],
            [
                'tenant_id' => $settings->tenantId(),
                'site_id' => $settings->siteId(),
                'late_tolerance_minutes' => $settings->lateToleranceMinutes(),
                'hourly_late_rate' => $settings->hourlyLateRate(),
                'daily_late_rate' => $settings->dailyLateRate(),
                'early_return_enabled' => $settings->earlyReturnEnabled(),
                'early_return_fee_type' => $settings->earlyReturnFeeType()->value,
                'early_return_fee_percentage' => $settings->earlyReturnFeePercentage(),
                'early_return_fee_fixed' => $settings->earlyReturnFeeFixed(),
                'max_rental_duration_days' => $settings->maxRentalDurationDays(),
                'min_reservation_hours_ahead' => $settings->minReservationHoursAhead(),
            ],
        );
    }

    public function findById(RentalSettingsId $id): ?RentalSettings
    {
        $model = RentalSettingsEloquentModel::find($id->value());

        return $model ? $this->toDomain($model) : null;
    }

    public function findByScope(?string $tenantId, ?string $siteId): ?RentalSettings
    {
        $query = RentalSettingsEloquentModel::query();

        if ($tenantId === null && $siteId === null) {
            $query->whereNull('tenant_id')->whereNull('site_id');
        } elseif ($siteId === null) {
            $query->where('tenant_id', $tenantId)->whereNull('site_id');
        } else {
            $query->where('tenant_id', $tenantId)->where('site_id', $siteId);
        }

        $model = $query->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function getEffectiveSettings(?string $tenantId = null, ?string $siteId = null): RentalSettings
    {
        // Try site-specific settings first
        if ($tenantId !== null && $siteId !== null) {
            $settings = $this->findBySiteId($tenantId, $siteId);
            if ($settings !== null) {
                return $settings;
            }
        }

        // Try tenant-specific settings
        if ($tenantId !== null) {
            $settings = $this->findByTenantId($tenantId);
            if ($settings !== null) {
                return $settings;
            }
        }

        // Try app default
        $settings = $this->findAppDefault();
        if ($settings !== null) {
            return $settings;
        }

        // Create default settings in memory if none exist
        return RentalSettings::create(
            id: RentalSettingsId::generate(),
            tenantId: null,
            siteId: null,
        );
    }

    public function findAppDefault(): ?RentalSettings
    {
        return $this->findByScope(null, null);
    }

    public function findByTenantId(string $tenantId): ?RentalSettings
    {
        return $this->findByScope($tenantId, null);
    }

    public function findBySiteId(string $tenantId, string $siteId): ?RentalSettings
    {
        return $this->findByScope($tenantId, $siteId);
    }

    public function delete(RentalSettingsId $id): void
    {
        RentalSettingsEloquentModel::destroy($id->value());
    }

    private function toDomain(RentalSettingsEloquentModel $model): RentalSettings
    {
        return RentalSettings::reconstitute(
            id: new RentalSettingsId($model->id),
            tenantId: $model->tenant_id,
            siteId: $model->site_id,
            lateToleranceMinutes: $model->late_tolerance_minutes,
            hourlyLateRate: $model->hourly_late_rate,
            dailyLateRate: $model->daily_late_rate,
            earlyReturnEnabled: $model->early_return_enabled,
            earlyReturnFeeType: EarlyReturnFeeType::from($model->early_return_fee_type),
            earlyReturnFeePercentage: $model->early_return_fee_percentage,
            earlyReturnFeeFixed: $model->early_return_fee_fixed,
            maxRentalDurationDays: $model->max_rental_duration_days,
            minReservationHoursAhead: $model->min_reservation_hours_ahead,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }
}
