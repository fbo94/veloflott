<?php

declare(strict_types=1);

namespace Tests\Unit\Pricing\Domain;

use Pricing\Domain\DiscountType;

describe('DiscountType', function () {
    describe('label', function () {
        it('returns correct label for percentage', function () {
            expect(DiscountType::PERCENTAGE->label())->toBe('Pourcentage');
        });

        it('returns correct label for fixed', function () {
            expect(DiscountType::FIXED->label())->toBe('Montant fixe');
        });
    });

    describe('cases', function () {
        it('returns all discount types', function () {
            $cases = DiscountType::cases();

            expect($cases)->toHaveCount(2);
            expect($cases)->toContain(DiscountType::PERCENTAGE);
            expect($cases)->toContain(DiscountType::FIXED);
        });
    });

    describe('from', function () {
        it('creates discount type from valid string', function () {
            expect(DiscountType::from('percentage'))->toBe(DiscountType::PERCENTAGE);
            expect(DiscountType::from('fixed'))->toBe(DiscountType::FIXED);
        });

        it('throws exception for invalid string', function () {
            expect(fn () => DiscountType::from('invalid'))->toThrow(\ValueError::class);
        });
    });
});
