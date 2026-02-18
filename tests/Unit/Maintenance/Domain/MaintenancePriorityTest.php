<?php

declare(strict_types=1);

namespace Tests\Unit\Maintenance\Domain;

use Maintenance\Domain\MaintenancePriority;

describe('MaintenancePriority', function () {
    describe('label', function () {
        it('returns correct label for normal', function () {
            expect(MaintenancePriority::NORMAL->label())->toBe('Normale');
        });

        it('returns correct label for urgent', function () {
            expect(MaintenancePriority::URGENT->label())->toBe('Urgente');
        });
    });

    describe('isUrgent', function () {
        it('returns true for urgent priority', function () {
            expect(MaintenancePriority::URGENT->isUrgent())->toBeTrue();
        });

        it('returns false for normal priority', function () {
            expect(MaintenancePriority::NORMAL->isUrgent())->toBeFalse();
        });
    });

    describe('cases', function () {
        it('returns all priorities', function () {
            $cases = MaintenancePriority::cases();

            expect($cases)->toHaveCount(2);
            expect($cases)->toContain(MaintenancePriority::NORMAL);
            expect($cases)->toContain(MaintenancePriority::URGENT);
        });
    });

    describe('from', function () {
        it('creates priority from valid string', function () {
            expect(MaintenancePriority::from('normal'))->toBe(MaintenancePriority::NORMAL);
            expect(MaintenancePriority::from('urgent'))->toBe(MaintenancePriority::URGENT);
        });

        it('throws exception for invalid string', function () {
            expect(fn () => MaintenancePriority::from('invalid'))->toThrow(\ValueError::class);
        });
    });
});
