<?php

declare(strict_types=1);

namespace Tests\Unit\Rental\Domain;

use Rental\Domain\EarlyReturnFeeType;
use Rental\Domain\RentalSettings;
use Rental\Domain\RentalSettingsId;

describe('RentalSettings', function () {
    describe('create', function () {
        it('creates rental settings with default values', function () {
            $id = RentalSettingsId::fromString('settings-123');
            $settings = RentalSettings::create(
                id: $id,
                tenantId: 'tenant-123',
                siteId: null,
            );

            expect($settings->id()->value())->toBe('settings-123');
            expect($settings->tenantId())->toBe('tenant-123');
            expect($settings->siteId())->toBeNull();
            expect($settings->lateToleranceMinutes())->toBe(30);
            expect($settings->hourlyLateRate())->toBe(10.00);
            expect($settings->dailyLateRate())->toBe(50.00);
            expect($settings->earlyReturnEnabled())->toBeTrue();
            expect($settings->earlyReturnFeeType())->toBe(EarlyReturnFeeType::PERCENTAGE);
            expect($settings->earlyReturnFeePercentage())->toBe(30.00);
            expect($settings->maxRentalDurationDays())->toBe(30);
            expect($settings->minReservationHoursAhead())->toBe(2);
        });

        it('creates rental settings with custom values', function () {
            $id = RentalSettingsId::fromString('settings-456');
            $settings = RentalSettings::create(
                id: $id,
                tenantId: 'tenant-456',
                siteId: 'site-123',
                lateToleranceMinutes: 60,
                hourlyLateRate: 15.00,
                dailyLateRate: 75.00,
                earlyReturnEnabled: false,
                earlyReturnFeeType: EarlyReturnFeeType::FIXED,
                earlyReturnFeePercentage: null,
                earlyReturnFeeFixed: 50.00,
                maxRentalDurationDays: 14,
                minReservationHoursAhead: 4,
            );

            expect($settings->lateToleranceMinutes())->toBe(60);
            expect($settings->hourlyLateRate())->toBe(15.00);
            expect($settings->earlyReturnEnabled())->toBeFalse();
            expect($settings->earlyReturnFeeType())->toBe(EarlyReturnFeeType::FIXED);
            expect($settings->maxRentalDurationDays())->toBe(14);
        });

        it('throws exception when site defined without tenant', function () {
            $id = RentalSettingsId::fromString('settings-123');

            expect(fn () => RentalSettings::create(
                id: $id,
                tenantId: null,
                siteId: 'site-123',
            ))->toThrow(\DomainException::class, 'Site cannot be defined without a tenant');
        });
    });

    describe('scope methods', function () {
        it('identifies app default settings', function () {
            $id = RentalSettingsId::fromString('settings-123');
            $settings = RentalSettings::create(
                id: $id,
                tenantId: null,
                siteId: null,
            );

            expect($settings->isAppDefault())->toBeTrue();
            expect($settings->isTenantDefault())->toBeFalse();
            expect($settings->isSiteSpecific())->toBeFalse();
        });

        it('identifies tenant default settings', function () {
            $id = RentalSettingsId::fromString('settings-123');
            $settings = RentalSettings::create(
                id: $id,
                tenantId: 'tenant-123',
                siteId: null,
            );

            expect($settings->isAppDefault())->toBeFalse();
            expect($settings->isTenantDefault())->toBeTrue();
            expect($settings->isSiteSpecific())->toBeFalse();
        });

        it('identifies site specific settings', function () {
            $id = RentalSettingsId::fromString('settings-123');
            $settings = RentalSettings::create(
                id: $id,
                tenantId: 'tenant-123',
                siteId: 'site-123',
            );

            expect($settings->isAppDefault())->toBeFalse();
            expect($settings->isTenantDefault())->toBeFalse();
            expect($settings->isSiteSpecific())->toBeTrue();
        });
    });

    describe('update', function () {
        it('updates rental settings', function () {
            $id = RentalSettingsId::fromString('settings-123');
            $settings = RentalSettings::create(
                id: $id,
                tenantId: 'tenant-123',
                siteId: null,
            );

            $settings->update(
                lateToleranceMinutes: 45,
                hourlyLateRate: 20.00,
                dailyLateRate: 100.00,
                earlyReturnEnabled: false,
                earlyReturnFeeType: EarlyReturnFeeType::FIXED,
                earlyReturnFeePercentage: null,
                earlyReturnFeeFixed: 75.00,
                maxRentalDurationDays: 21,
                minReservationHoursAhead: 6,
            );

            expect($settings->lateToleranceMinutes())->toBe(45);
            expect($settings->hourlyLateRate())->toBe(20.00);
            expect($settings->dailyLateRate())->toBe(100.00);
            expect($settings->earlyReturnEnabled())->toBeFalse();
            expect($settings->maxRentalDurationDays())->toBe(21);
        });
    });

    describe('timestamps', function () {
        it('creates timestamps on instantiation', function () {
            $id = RentalSettingsId::fromString('settings-123');
            $settings = RentalSettings::create(
                id: $id,
                tenantId: 'tenant-123',
                siteId: null,
            );

            expect($settings->createdAt())->toBeInstanceOf(\DateTimeImmutable::class);
            expect($settings->updatedAt())->toBeInstanceOf(\DateTimeImmutable::class);
        });
    });
});
