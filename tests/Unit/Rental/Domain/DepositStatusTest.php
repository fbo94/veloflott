<?php

declare(strict_types=1);

namespace Tests\Unit\Rental\Domain;

use Rental\Domain\DepositStatus;

describe('DepositStatus', function () {
    describe('cases', function () {
        it('returns all deposit statuses', function () {
            $cases = DepositStatus::cases();

            expect($cases)->toHaveCount(4);
            expect($cases)->toContain(DepositStatus::HELD);
            expect($cases)->toContain(DepositStatus::RELEASED);
            expect($cases)->toContain(DepositStatus::PARTIAL);
            expect($cases)->toContain(DepositStatus::RETAINED);
        });
    });

    describe('from', function () {
        it('creates status from valid string', function () {
            expect(DepositStatus::from('held'))->toBe(DepositStatus::HELD);
            expect(DepositStatus::from('released'))->toBe(DepositStatus::RELEASED);
            expect(DepositStatus::from('partial'))->toBe(DepositStatus::PARTIAL);
            expect(DepositStatus::from('retained'))->toBe(DepositStatus::RETAINED);
        });

        it('throws exception for invalid string', function () {
            expect(fn () => DepositStatus::from('invalid'))->toThrow(\ValueError::class);
        });
    });

    describe('values', function () {
        it('has correct string values', function () {
            expect(DepositStatus::HELD->value)->toBe('held');
            expect(DepositStatus::RELEASED->value)->toBe('released');
            expect(DepositStatus::PARTIAL->value)->toBe('partial');
            expect(DepositStatus::RETAINED->value)->toBe('retained');
        });
    });
});
