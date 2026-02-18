<?php

declare(strict_types=1);

namespace Tests\Unit\Pricing\Domain;

use Pricing\Domain\DiscountRule;
use Pricing\Domain\DiscountType;

describe('DiscountRule', function () {
    describe('create', function () {
        it('creates a discount rule with minDays', function () {
            $rule = DiscountRule::create(
                id: 'rule-123',
                categoryId: null,
                pricingClassId: null,
                minDays: 7,
                minDurationId: null,
                discountType: DiscountType::PERCENTAGE,
                discountValue: 10.0,
                label: 'Réduction semaine',
            );

            expect($rule->id())->toBe('rule-123');
            expect($rule->minDays())->toBe(7);
            expect($rule->discountType())->toBe(DiscountType::PERCENTAGE);
            expect($rule->discountValue())->toBe(10.0);
            expect($rule->label())->toBe('Réduction semaine');
            expect($rule->isActive())->toBeTrue();
            expect($rule->isCumulative())->toBeFalse();
        });

        it('creates a discount rule with minDurationId', function () {
            $rule = DiscountRule::create(
                id: 'rule-456',
                categoryId: 'cat-123',
                pricingClassId: 'class-123',
                minDays: null,
                minDurationId: 'dur-123',
                discountType: DiscountType::FIXED,
                discountValue: 50.0,
                label: 'Réduction fixe',
                description: 'Réduction de 50€',
                isCumulative: true,
                priority: 10,
            );

            expect($rule->categoryId())->toBe('cat-123');
            expect($rule->pricingClassId())->toBe('class-123');
            expect($rule->minDurationId())->toBe('dur-123');
            expect($rule->discountType())->toBe(DiscountType::FIXED);
            expect($rule->description())->toBe('Réduction de 50€');
            expect($rule->isCumulative())->toBeTrue();
            expect($rule->priority())->toBe(10);
        });
    });

    describe('validation', function () {
        it('throws exception when neither minDays nor minDurationId specified', function () {
            expect(fn () => DiscountRule::create(
                id: 'rule-123',
                categoryId: null,
                pricingClassId: null,
                minDays: null,
                minDurationId: null,
                discountType: DiscountType::PERCENTAGE,
                discountValue: 10.0,
                label: 'Réduction',
            ))->toThrow(\DomainException::class, 'Discount rule must have either minDays or minDurationId specified');
        });

        it('throws exception for minDays less than 1', function () {
            expect(fn () => DiscountRule::create(
                id: 'rule-123',
                categoryId: null,
                pricingClassId: null,
                minDays: 0,
                minDurationId: null,
                discountType: DiscountType::PERCENTAGE,
                discountValue: 10.0,
                label: 'Réduction',
            ))->toThrow(\DomainException::class, 'Minimum days must be at least 1');
        });

        it('throws exception for discount value <= 0', function () {
            expect(fn () => DiscountRule::create(
                id: 'rule-123',
                categoryId: null,
                pricingClassId: null,
                minDays: 7,
                minDurationId: null,
                discountType: DiscountType::PERCENTAGE,
                discountValue: 0,
                label: 'Réduction',
            ))->toThrow(\DomainException::class, 'Discount value must be greater than 0');
        });

        it('throws exception for percentage > 100', function () {
            expect(fn () => DiscountRule::create(
                id: 'rule-123',
                categoryId: null,
                pricingClassId: null,
                minDays: 7,
                minDurationId: null,
                discountType: DiscountType::PERCENTAGE,
                discountValue: 150.0,
                label: 'Réduction',
            ))->toThrow(\DomainException::class, 'Percentage discount cannot exceed 100%');
        });

        it('throws exception for empty label', function () {
            expect(fn () => DiscountRule::create(
                id: 'rule-123',
                categoryId: null,
                pricingClassId: null,
                minDays: 7,
                minDurationId: null,
                discountType: DiscountType::PERCENTAGE,
                discountValue: 10.0,
                label: '',
            ))->toThrow(\DomainException::class, 'Discount label cannot be empty');
        });

        it('throws exception for label exceeding 100 characters', function () {
            expect(fn () => DiscountRule::create(
                id: 'rule-123',
                categoryId: null,
                pricingClassId: null,
                minDays: 7,
                minDurationId: null,
                discountType: DiscountType::PERCENTAGE,
                discountValue: 10.0,
                label: str_repeat('a', 101),
            ))->toThrow(\DomainException::class, 'Discount label cannot exceed 100 characters');
        });
    });

    describe('appliesToCategory', function () {
        it('returns true when no category restriction', function () {
            $rule = DiscountRule::create(
                id: 'rule-123',
                categoryId: null,
                pricingClassId: null,
                minDays: 7,
                minDurationId: null,
                discountType: DiscountType::PERCENTAGE,
                discountValue: 10.0,
                label: 'Réduction',
            );

            expect($rule->appliesToCategory('any-category'))->toBeTrue();
        });

        it('returns true when category matches', function () {
            $rule = DiscountRule::create(
                id: 'rule-123',
                categoryId: 'cat-123',
                pricingClassId: null,
                minDays: 7,
                minDurationId: null,
                discountType: DiscountType::PERCENTAGE,
                discountValue: 10.0,
                label: 'Réduction',
            );

            expect($rule->appliesToCategory('cat-123'))->toBeTrue();
        });

        it('returns false when category does not match', function () {
            $rule = DiscountRule::create(
                id: 'rule-123',
                categoryId: 'cat-123',
                pricingClassId: null,
                minDays: 7,
                minDurationId: null,
                discountType: DiscountType::PERCENTAGE,
                discountValue: 10.0,
                label: 'Réduction',
            );

            expect($rule->appliesToCategory('cat-456'))->toBeFalse();
        });
    });

    describe('appliesToPricingClass', function () {
        it('returns true when no pricing class restriction', function () {
            $rule = DiscountRule::create(
                id: 'rule-123',
                categoryId: null,
                pricingClassId: null,
                minDays: 7,
                minDurationId: null,
                discountType: DiscountType::PERCENTAGE,
                discountValue: 10.0,
                label: 'Réduction',
            );

            expect($rule->appliesToPricingClass('any-class'))->toBeTrue();
        });

        it('returns true when pricing class matches', function () {
            $rule = DiscountRule::create(
                id: 'rule-123',
                categoryId: null,
                pricingClassId: 'class-123',
                minDays: 7,
                minDurationId: null,
                discountType: DiscountType::PERCENTAGE,
                discountValue: 10.0,
                label: 'Réduction',
            );

            expect($rule->appliesToPricingClass('class-123'))->toBeTrue();
        });

        it('returns false when pricing class does not match', function () {
            $rule = DiscountRule::create(
                id: 'rule-123',
                categoryId: null,
                pricingClassId: 'class-123',
                minDays: 7,
                minDurationId: null,
                discountType: DiscountType::PERCENTAGE,
                discountValue: 10.0,
                label: 'Réduction',
            );

            expect($rule->appliesToPricingClass('class-456'))->toBeFalse();
        });
    });

    describe('appliesToDays', function () {
        it('returns true when no minDays restriction', function () {
            $rule = DiscountRule::create(
                id: 'rule-123',
                categoryId: null,
                pricingClassId: null,
                minDays: null,
                minDurationId: 'dur-123',
                discountType: DiscountType::PERCENTAGE,
                discountValue: 10.0,
                label: 'Réduction',
            );

            expect($rule->appliesToDays(1))->toBeTrue();
        });

        it('returns true when days meet minimum', function () {
            $rule = DiscountRule::create(
                id: 'rule-123',
                categoryId: null,
                pricingClassId: null,
                minDays: 7,
                minDurationId: null,
                discountType: DiscountType::PERCENTAGE,
                discountValue: 10.0,
                label: 'Réduction',
            );

            expect($rule->appliesToDays(7))->toBeTrue();
            expect($rule->appliesToDays(10))->toBeTrue();
        });

        it('returns false when days below minimum', function () {
            $rule = DiscountRule::create(
                id: 'rule-123',
                categoryId: null,
                pricingClassId: null,
                minDays: 7,
                minDurationId: null,
                discountType: DiscountType::PERCENTAGE,
                discountValue: 10.0,
                label: 'Réduction',
            );

            expect($rule->appliesToDays(5))->toBeFalse();
        });
    });

    describe('calculateDiscount', function () {
        it('calculates percentage discount', function () {
            $rule = DiscountRule::create(
                id: 'rule-123',
                categoryId: null,
                pricingClassId: null,
                minDays: 7,
                minDurationId: null,
                discountType: DiscountType::PERCENTAGE,
                discountValue: 10.0,
                label: 'Réduction',
            );

            expect($rule->calculateDiscount(100.0))->toBe(10.0);
            expect($rule->calculateDiscount(250.0))->toBe(25.0);
        });

        it('calculates fixed discount', function () {
            $rule = DiscountRule::create(
                id: 'rule-123',
                categoryId: null,
                pricingClassId: null,
                minDays: 7,
                minDurationId: null,
                discountType: DiscountType::FIXED,
                discountValue: 50.0,
                label: 'Réduction',
            );

            expect($rule->calculateDiscount(100.0))->toBe(50.0);
            expect($rule->calculateDiscount(250.0))->toBe(50.0);
        });

        it('caps fixed discount at base price', function () {
            $rule = DiscountRule::create(
                id: 'rule-123',
                categoryId: null,
                pricingClassId: null,
                minDays: 7,
                minDurationId: null,
                discountType: DiscountType::FIXED,
                discountValue: 100.0,
                label: 'Réduction',
            );

            expect($rule->calculateDiscount(50.0))->toBe(50.0);
        });
    });

    describe('activate', function () {
        it('activates an inactive rule', function () {
            $rule = DiscountRule::create(
                id: 'rule-123',
                categoryId: null,
                pricingClassId: null,
                minDays: 7,
                minDurationId: null,
                discountType: DiscountType::PERCENTAGE,
                discountValue: 10.0,
                label: 'Réduction',
            );
            $rule->deactivate();

            $rule->activate();

            expect($rule->isActive())->toBeTrue();
        });

        it('throws exception when already active', function () {
            $rule = DiscountRule::create(
                id: 'rule-123',
                categoryId: null,
                pricingClassId: null,
                minDays: 7,
                minDurationId: null,
                discountType: DiscountType::PERCENTAGE,
                discountValue: 10.0,
                label: 'Réduction',
            );

            expect(fn () => $rule->activate())
                ->toThrow(\DomainException::class, 'Discount rule is already active');
        });
    });

    describe('deactivate', function () {
        it('deactivates an active rule', function () {
            $rule = DiscountRule::create(
                id: 'rule-123',
                categoryId: null,
                pricingClassId: null,
                minDays: 7,
                minDurationId: null,
                discountType: DiscountType::PERCENTAGE,
                discountValue: 10.0,
                label: 'Réduction',
            );

            $rule->deactivate();

            expect($rule->isActive())->toBeFalse();
        });

        it('throws exception when already inactive', function () {
            $rule = DiscountRule::create(
                id: 'rule-123',
                categoryId: null,
                pricingClassId: null,
                minDays: 7,
                minDurationId: null,
                discountType: DiscountType::PERCENTAGE,
                discountValue: 10.0,
                label: 'Réduction',
            );
            $rule->deactivate();

            expect(fn () => $rule->deactivate())
                ->toThrow(\DomainException::class, 'Discount rule is already inactive');
        });
    });
});
