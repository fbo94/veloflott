<?php

declare(strict_types=1);

namespace Tests\Unit\Fleet\Domain;

use Fleet\Domain\PricingTier;

describe('PricingTier', function () {
    describe('label', function () {
        it('returns correct label for standard', function () {
            expect(PricingTier::STANDARD->label())->toBe('Standard');
        });

        it('returns correct label for premium', function () {
            expect(PricingTier::PREMIUM->label())->toBe('Premium');
        });
    });

    describe('description', function () {
        it('returns correct description for standard', function () {
            expect(PricingTier::STANDARD->description())->toBe('Tarif standard pour vélos classiques');
        });

        it('returns correct description for premium', function () {
            expect(PricingTier::PREMIUM->description())->toBe('Tarif premium pour vélos haut de gamme');
        });
    });

    describe('cases', function () {
        it('returns all pricing tiers', function () {
            $cases = PricingTier::cases();

            expect($cases)->toHaveCount(2);
            expect($cases)->toContain(PricingTier::STANDARD);
            expect($cases)->toContain(PricingTier::PREMIUM);
        });
    });

    describe('from', function () {
        it('creates tier from valid string', function () {
            expect(PricingTier::from('standard'))->toBe(PricingTier::STANDARD);
            expect(PricingTier::from('premium'))->toBe(PricingTier::PREMIUM);
        });

        it('throws exception for invalid string', function () {
            expect(fn () => PricingTier::from('invalid'))->toThrow(\ValueError::class);
        });
    });
});
