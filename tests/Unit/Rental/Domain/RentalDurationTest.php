<?php

declare(strict_types=1);

namespace Tests\Unit\Rental\Domain;

use Rental\Domain\RentalDuration;

describe('RentalDuration', function () {
    describe('hours', function () {
        it('returns correct hours for half day', function () {
            expect(RentalDuration::HALF_DAY->hours())->toBe(4);
        });

        it('returns correct hours for full day', function () {
            expect(RentalDuration::FULL_DAY->hours())->toBe(8);
        });

        it('returns correct hours for two days', function () {
            expect(RentalDuration::TWO_DAYS->hours())->toBe(48);
        });

        it('returns correct hours for three days', function () {
            expect(RentalDuration::THREE_DAYS->hours())->toBe(72);
        });

        it('returns correct hours for week', function () {
            expect(RentalDuration::WEEK->hours())->toBe(168);
        });

        it('returns zero for custom', function () {
            expect(RentalDuration::CUSTOM->hours())->toBe(0);
        });
    });

    describe('days', function () {
        it('calculates days from hours for half day', function () {
            expect(RentalDuration::HALF_DAY->days())->toBe(4 / 24);
        });

        it('calculates days from hours for full day', function () {
            expect(RentalDuration::FULL_DAY->days())->toBe(8 / 24);
        });

        it('calculates days from hours for two days', function () {
            expect(RentalDuration::TWO_DAYS->days())->toBe(2.0);
        });

        it('calculates days from hours for week', function () {
            expect(RentalDuration::WEEK->days())->toBe(7.0);
        });

        it('returns zero for custom', function () {
            expect(RentalDuration::CUSTOM->days())->toBe(0.0);
        });
    });

    describe('cases', function () {
        it('returns all durations', function () {
            $cases = RentalDuration::cases();

            expect($cases)->toHaveCount(6);
            expect($cases)->toContain(RentalDuration::HALF_DAY);
            expect($cases)->toContain(RentalDuration::FULL_DAY);
            expect($cases)->toContain(RentalDuration::TWO_DAYS);
            expect($cases)->toContain(RentalDuration::THREE_DAYS);
            expect($cases)->toContain(RentalDuration::WEEK);
            expect($cases)->toContain(RentalDuration::CUSTOM);
        });
    });

    describe('from', function () {
        it('creates duration from valid string', function () {
            expect(RentalDuration::from('half_day'))->toBe(RentalDuration::HALF_DAY);
            expect(RentalDuration::from('full_day'))->toBe(RentalDuration::FULL_DAY);
            expect(RentalDuration::from('week'))->toBe(RentalDuration::WEEK);
            expect(RentalDuration::from('custom'))->toBe(RentalDuration::CUSTOM);
        });

        it('throws exception for invalid string', function () {
            expect(fn () => RentalDuration::from('invalid'))->toThrow(\ValueError::class);
        });
    });
});
