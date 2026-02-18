<?php

declare(strict_types=1);

namespace Tests\Unit\Fleet\Domain;

use Fleet\Domain\RetirementReason;

describe('RetirementReason', function () {
    describe('label', function () {
        it('returns correct label for sold', function () {
            expect(RetirementReason::SOLD->label())->toBe('Vendu');
        });

        it('returns correct label for stolen', function () {
            expect(RetirementReason::STOLEN->label())->toBe('Volé');
        });

        it('returns correct label for permanently out of service', function () {
            expect(RetirementReason::PERMANENTLY_OUT_OF_SERVICE->label())->toBe('Hors service définitif');
        });

        it('returns correct label for other', function () {
            expect(RetirementReason::OTHER->label())->toBe('Autre');
        });
    });

    describe('cases', function () {
        it('returns all retirement reasons', function () {
            $cases = RetirementReason::cases();

            expect($cases)->toHaveCount(4);
            expect($cases)->toContain(RetirementReason::SOLD);
            expect($cases)->toContain(RetirementReason::STOLEN);
            expect($cases)->toContain(RetirementReason::PERMANENTLY_OUT_OF_SERVICE);
            expect($cases)->toContain(RetirementReason::OTHER);
        });
    });

    describe('from', function () {
        it('creates reason from valid string', function () {
            expect(RetirementReason::from('sold'))->toBe(RetirementReason::SOLD);
            expect(RetirementReason::from('stolen'))->toBe(RetirementReason::STOLEN);
        });

        it('throws exception for invalid string', function () {
            expect(fn () => RetirementReason::from('invalid'))->toThrow(\ValueError::class);
        });
    });
});
