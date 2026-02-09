<?php

declare(strict_types=1);

namespace Rental\Interface\Http\RentalSettings;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Rental\Domain\Repository\RentalSettingsRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;

final class GetRentalSettingsController
{
    public function __construct(
        private readonly RentalSettingsRepositoryInterface $settingsRepository,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $tenantId = $request->query('tenant_id');
        $siteId = $request->query('site_id');

        $settings = $this->settingsRepository->getEffectiveSettings($tenantId, $siteId);

        return new JsonResponse([
            'id' => $settings->id()->value(),
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
            'scope' => $settings->isSiteSpecific() ? 'site' : ($settings->isTenantDefault() ? 'tenant' : 'app'),
        ], Response::HTTP_OK);
    }
}
