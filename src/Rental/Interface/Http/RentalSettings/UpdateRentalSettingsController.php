<?php

declare(strict_types=1);

namespace Rental\Interface\Http\RentalSettings;

use Illuminate\Http\JsonResponse;
use Rental\Domain\EarlyReturnFeeType;
use Rental\Domain\RentalSettings;
use Rental\Domain\RentalSettingsId;
use Rental\Domain\Repository\RentalSettingsRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;

final class UpdateRentalSettingsController
{
    public function __construct(
        private readonly RentalSettingsRepositoryInterface $settingsRepository,
    ) {
    }

    public function __invoke(UpdateRentalSettingsRequest $request): JsonResponse
    {
        $tenantId = $request->input('tenant_id');
        $siteId = $request->input('site_id');

        // Try to find existing settings for this scope
        $existingSettings = $this->settingsRepository->findByScope($tenantId, $siteId);

        try {
            if ($existingSettings !== null) {
                // Update existing settings
                $existingSettings->update(
                    lateToleranceMinutes: (int) $request->input('late_tolerance_minutes'),
                    hourlyLateRate: (float) $request->input('hourly_late_rate'),
                    dailyLateRate: (float) $request->input('daily_late_rate'),
                    earlyReturnEnabled: (bool) $request->input('early_return_enabled'),
                    earlyReturnFeeType: EarlyReturnFeeType::from($request->input('early_return_fee_type')),
                    earlyReturnFeePercentage: $request->input('early_return_fee_percentage') !== null
                        ? (float) $request->input('early_return_fee_percentage')
                        : null,
                    earlyReturnFeeFixed: $request->input('early_return_fee_fixed') !== null
                        ? (float) $request->input('early_return_fee_fixed')
                        : null,
                    maxRentalDurationDays: (int) $request->input('max_rental_duration_days'),
                    minReservationHoursAhead: (int) $request->input('min_reservation_hours_ahead'),
                );

                $this->settingsRepository->save($existingSettings);

                return new JsonResponse([
                    'id' => $existingSettings->id()->value(),
                    'message' => 'Rental settings updated successfully',
                ], Response::HTTP_OK);
            }

            // Create new settings
            $settings = RentalSettings::create(
                id: RentalSettingsId::generate(),
                tenantId: $tenantId,
                siteId: $siteId,
                lateToleranceMinutes: (int) $request->input('late_tolerance_minutes'),
                hourlyLateRate: (float) $request->input('hourly_late_rate'),
                dailyLateRate: (float) $request->input('daily_late_rate'),
                earlyReturnEnabled: (bool) $request->input('early_return_enabled'),
                earlyReturnFeeType: EarlyReturnFeeType::from($request->input('early_return_fee_type')),
                earlyReturnFeePercentage: $request->input('early_return_fee_percentage') !== null
                    ? (float) $request->input('early_return_fee_percentage')
                    : null,
                earlyReturnFeeFixed: $request->input('early_return_fee_fixed') !== null
                    ? (float) $request->input('early_return_fee_fixed')
                    : null,
                maxRentalDurationDays: (int) $request->input('max_rental_duration_days'),
                minReservationHoursAhead: (int) $request->input('min_reservation_hours_ahead'),
            );

            $this->settingsRepository->save($settings);

            return new JsonResponse([
                'id' => $settings->id()->value(),
                'message' => 'Rental settings created successfully',
            ], Response::HTTP_CREATED);
        } catch (\DomainException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
