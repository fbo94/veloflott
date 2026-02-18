<?php

declare(strict_types=1);

namespace Tests\Unit\Maintenance\Domain;

use Maintenance\Domain\MaintenanceType;

describe('MaintenanceType', function () {
    describe('label', function () {
        it('returns correct label for preventive', function () {
            expect(MaintenanceType::PREVENTIVE->label())->toBe('Préventive');
        });

        it('returns correct label for curative', function () {
            expect(MaintenanceType::CURATIVE->label())->toBe('Curative');
        });
    });

    describe('cases', function () {
        it('returns all types', function () {
            $cases = MaintenanceType::cases();

            expect($cases)->toHaveCount(2);
            expect($cases)->toContain(MaintenanceType::PREVENTIVE);
            expect($cases)->toContain(MaintenanceType::CURATIVE);
        });
    });

    describe('from', function () {
        it('creates type from valid string', function () {
            expect(MaintenanceType::from('preventive'))->toBe(MaintenanceType::PREVENTIVE);
            expect(MaintenanceType::from('curative'))->toBe(MaintenanceType::CURATIVE);
        });

        it('throws exception for invalid string', function () {
            expect(fn () => MaintenanceType::from('invalid'))->toThrow(\ValueError::class);
        });
    });
});
