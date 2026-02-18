<?php

declare(strict_types=1);

namespace Tests\Unit\Fleet\Domain;

use Fleet\Domain\BikeCategory;

describe('BikeCategory', function () {
    describe('label', function () {
        it('returns correct label for enduro', function () {
            expect(BikeCategory::ENDURO->label())->toBe('Enduro');
        });

        it('returns correct label for road', function () {
            expect(BikeCategory::ROAD->label())->toBe('Route');
        });

        it('returns correct label for gravel', function () {
            expect(BikeCategory::GRAVEL->label())->toBe('Gravel');
        });

        it('returns correct label for emtb', function () {
            expect(BikeCategory::EMTB->label())->toBe('VTTAE');
        });

        it('returns correct label for ebike', function () {
            expect(BikeCategory::EBIKE->label())->toBe('Vélo électrique');
        });

        it('returns correct label for misc', function () {
            expect(BikeCategory::MISC->label())->toBe('Divers');
        });
    });

    describe('isDefault', function () {
        it('returns true for all categories', function () {
            foreach (BikeCategory::cases() as $category) {
                expect($category->isDefault())->toBeTrue();
            }
        });
    });

    describe('cases', function () {
        it('returns all categories', function () {
            $cases = BikeCategory::cases();

            expect($cases)->toHaveCount(13);
            expect($cases)->toContain(BikeCategory::ENDURO);
            expect($cases)->toContain(BikeCategory::DH);
            expect($cases)->toContain(BikeCategory::XC);
            expect($cases)->toContain(BikeCategory::ROAD);
            expect($cases)->toContain(BikeCategory::GRAVEL);
        });
    });

    describe('from', function () {
        it('creates category from valid string', function () {
            expect(BikeCategory::from('enduro'))->toBe(BikeCategory::ENDURO);
            expect(BikeCategory::from('road'))->toBe(BikeCategory::ROAD);
            expect(BikeCategory::from('gravel'))->toBe(BikeCategory::GRAVEL);
        });

        it('throws exception for invalid string', function () {
            expect(fn () => BikeCategory::from('invalid'))->toThrow(\ValueError::class);
        });
    });
});
