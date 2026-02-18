<?php

declare(strict_types=1);

namespace Tests\Unit\Rental\Domain;

use Rental\Domain\BikeCondition;

describe('BikeCondition', function () {
    describe('label', function () {
        it('returns correct label for ok', function () {
            expect(BikeCondition::OK->label())->toBe('OK');
        });

        it('returns correct label for minor damage', function () {
            expect(BikeCondition::MINOR_DAMAGE->label())->toBe('Dégâts mineurs');
        });

        it('returns correct label for major damage', function () {
            expect(BikeCondition::MAJOR_DAMAGE->label())->toBe('Dégâts majeurs');
        });
    });

    describe('requiresMaintenance', function () {
        it('returns false for ok condition', function () {
            expect(BikeCondition::OK->requiresMaintenance())->toBeFalse();
        });

        it('returns true for minor damage', function () {
            expect(BikeCondition::MINOR_DAMAGE->requiresMaintenance())->toBeTrue();
        });

        it('returns true for major damage', function () {
            expect(BikeCondition::MAJOR_DAMAGE->requiresMaintenance())->toBeTrue();
        });
    });

    describe('cases', function () {
        it('returns all conditions', function () {
            $cases = BikeCondition::cases();

            expect($cases)->toHaveCount(3);
            expect($cases)->toContain(BikeCondition::OK);
            expect($cases)->toContain(BikeCondition::MINOR_DAMAGE);
            expect($cases)->toContain(BikeCondition::MAJOR_DAMAGE);
        });
    });

    describe('from', function () {
        it('creates condition from valid string', function () {
            expect(BikeCondition::from('ok'))->toBe(BikeCondition::OK);
            expect(BikeCondition::from('minor_damage'))->toBe(BikeCondition::MINOR_DAMAGE);
            expect(BikeCondition::from('major_damage'))->toBe(BikeCondition::MAJOR_DAMAGE);
        });

        it('throws exception for invalid string', function () {
            expect(fn () => BikeCondition::from('invalid'))->toThrow(\ValueError::class);
        });
    });
});
