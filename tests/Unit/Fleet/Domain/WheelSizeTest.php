<?php

declare(strict_types=1);

namespace Tests\Unit\Fleet\Domain;

use Fleet\Domain\WheelSize;

describe('WheelSize', function () {
    describe('label', function () {
        it('returns correct label for 26', function () {
            expect(WheelSize::TWENTY_SIX->label())->toBe('26"');
        });

        it('returns correct label for 27.5', function () {
            expect(WheelSize::TWENTY_SEVEN_FIVE->label())->toBe('27.5"');
        });

        it('returns correct label for 29', function () {
            expect(WheelSize::TWENTY_NINE->label())->toBe('29"');
        });

        it('returns correct label for 700c', function () {
            expect(WheelSize::SEVEN_HUNDRED->label())->toBe('700c');
        });

        it('returns correct label for 650b', function () {
            expect(WheelSize::SIX_HUNDRED_FIFTY->label())->toBe('650b');
        });

        it('returns correct label for 32mm', function () {
            expect(WheelSize::THIRTY_TWO->label())->toBe('32mm');
        });
    });

    describe('cases', function () {
        it('returns all wheel sizes', function () {
            $cases = WheelSize::cases();

            expect($cases)->toHaveCount(6);
            expect($cases)->toContain(WheelSize::TWENTY_SIX);
            expect($cases)->toContain(WheelSize::TWENTY_SEVEN_FIVE);
            expect($cases)->toContain(WheelSize::TWENTY_NINE);
            expect($cases)->toContain(WheelSize::SEVEN_HUNDRED);
        });
    });

    describe('from', function () {
        it('creates wheel size from valid string', function () {
            expect(WheelSize::from('26'))->toBe(WheelSize::TWENTY_SIX);
            expect(WheelSize::from('27.5'))->toBe(WheelSize::TWENTY_SEVEN_FIVE);
            expect(WheelSize::from('29'))->toBe(WheelSize::TWENTY_NINE);
            expect(WheelSize::from('700c'))->toBe(WheelSize::SEVEN_HUNDRED);
        });

        it('throws exception for invalid string', function () {
            expect(fn () => WheelSize::from('invalid'))->toThrow(\ValueError::class);
        });
    });
});
