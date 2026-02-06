<?php

declare(strict_types=1);

use Fleet\Domain\DiscountRule;
use Fleet\Domain\DiscountType;

test('can create a percentage discount rule', function () {
    $rule = DiscountRule::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        categoryId: null,  // Applies to all categories
        pricingClassId: 'class-premium',
        minDays: 3,
        minDurationId: null,
        discountType: DiscountType::PERCENTAGE,
        discountValue: 15.0,
        label: 'Réduction longue durée -15%',
    );

    expect($rule->id())->toBe('123e4567-e89b-12d3-a456-426614174000');
    expect($rule->categoryId())->toBeNull();
    expect($rule->pricingClassId())->toBe('class-premium');
    expect($rule->minDays())->toBe(3);
    expect($rule->discountType())->toBe(DiscountType::PERCENTAGE);
    expect($rule->discountValue())->toBe(15.0);
    expect($rule->label())->toBe('Réduction longue durée -15%');
    expect($rule->isActive())->toBeTrue();
    expect($rule->isCumulative())->toBeFalse();
});

test('can create a fixed amount discount rule', function () {
    $rule = DiscountRule::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        categoryId: 'cat-vtt',
        pricingClassId: null,
        minDays: 7,
        minDurationId: null,
        discountType: DiscountType::FIXED,
        discountValue: 20.0,
        label: 'Réduction semaine -20€',
    );

    expect($rule->discountType())->toBe(DiscountType::FIXED);
    expect($rule->discountValue())->toBe(20.0);
});

test('applies to all categories when categoryId is null', function () {
    $rule = DiscountRule::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        categoryId: null,
        pricingClassId: null,
        minDays: 3,
        minDurationId: null,
        discountType: DiscountType::PERCENTAGE,
        discountValue: 10.0,
        label: 'Réduction générale',
    );

    expect($rule->appliesToCategory('any-category-id'))->toBeTrue();
    expect($rule->appliesToCategory(null))->toBeTrue();
});

test('applies only to specific category when categoryId is set', function () {
    $rule = DiscountRule::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        categoryId: 'cat-vtt',
        pricingClassId: null,
        minDays: 3,
        minDurationId: null,
        discountType: DiscountType::PERCENTAGE,
        discountValue: 10.0,
        label: 'Réduction VTT',
    );

    expect($rule->appliesToCategory('cat-vtt'))->toBeTrue();
    expect($rule->appliesToCategory('cat-route'))->toBeFalse();
});

test('applies to all pricing classes when pricingClassId is null', function () {
    $rule = DiscountRule::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        categoryId: null,
        pricingClassId: null,
        minDays: 3,
        minDurationId: null,
        discountType: DiscountType::PERCENTAGE,
        discountValue: 10.0,
        label: 'Réduction générale',
    );

    expect($rule->appliesToPricingClass('any-class-id'))->toBeTrue();
});

test('applies only to specific pricing class when pricingClassId is set', function () {
    $rule = DiscountRule::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        categoryId: null,
        pricingClassId: 'class-premium',
        minDays: 3,
        minDurationId: null,
        discountType: DiscountType::PERCENTAGE,
        discountValue: 10.0,
        label: 'Réduction premium',
    );

    expect($rule->appliesToPricingClass('class-premium'))->toBeTrue();
    expect($rule->appliesToPricingClass('class-standard'))->toBeFalse();
});

test('applies when days meet minimum requirement', function () {
    $rule = DiscountRule::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        categoryId: null,
        pricingClassId: null,
        minDays: 3,
        minDurationId: null,
        discountType: DiscountType::PERCENTAGE,
        discountValue: 10.0,
        label: 'Réduction 3+ jours',
    );

    expect($rule->appliesToDays(2))->toBeFalse();
    expect($rule->appliesToDays(3))->toBeTrue();
    expect($rule->appliesToDays(7))->toBeTrue();
});

test('calculates percentage discount correctly', function () {
    $rule = DiscountRule::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        categoryId: null,
        pricingClassId: null,
        minDays: 3,
        minDurationId: null,
        discountType: DiscountType::PERCENTAGE,
        discountValue: 15.0,  // 15%
        label: 'Réduction 15%',
    );

    expect($rule->calculateDiscount(100.0))->toBe(15.0);
    expect($rule->calculateDiscount(200.0))->toBe(30.0);
});

test('calculates fixed discount correctly', function () {
    $rule = DiscountRule::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        categoryId: null,
        pricingClassId: null,
        minDays: 3,
        minDurationId: null,
        discountType: DiscountType::FIXED,
        discountValue: 20.0,  // 20€
        label: 'Réduction 20€',
    );

    expect($rule->calculateDiscount(100.0))->toBe(20.0);
    expect($rule->calculateDiscount(200.0))->toBe(20.0);
});

test('fixed discount cannot exceed base price', function () {
    $rule = DiscountRule::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        categoryId: null,
        pricingClassId: null,
        minDays: 3,
        minDurationId: null,
        discountType: DiscountType::FIXED,
        discountValue: 50.0,  // 50€
        label: 'Réduction 50€',
    );

    // Discount is capped at base price (30€)
    expect($rule->calculateDiscount(30.0))->toBe(30.0);
});

test('can update discount rule', function () {
    $rule = DiscountRule::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        categoryId: null,
        pricingClassId: null,
        minDays: 3,
        minDurationId: null,
        discountType: DiscountType::PERCENTAGE,
        discountValue: 10.0,
        label: 'Réduction 10%',
    );

    $rule->update(
        categoryId: 'cat-vtt',
        pricingClassId: 'class-premium',
        minDays: 5,
        minDurationId: null,
        discountType: DiscountType::PERCENTAGE,
        discountValue: 20.0,
        label: 'Réduction 20% VTT Premium',
        description: 'Pour locations longues',
        isCumulative: true,
        priority: 2,
    );

    expect($rule->label())->toBe('Réduction 20% VTT Premium');
    expect($rule->discountValue())->toBe(20.0);
    expect($rule->minDays())->toBe(5);
    expect($rule->isCumulative())->toBeTrue();
});

test('throws exception when neither minDays nor minDurationId are specified', function () {
    DiscountRule::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        categoryId: null,
        pricingClassId: null,
        minDays: null,
        minDurationId: null,
        discountType: DiscountType::PERCENTAGE,
        discountValue: 10.0,
        label: 'Invalid',
    );
})->throws(DomainException::class, 'minDays or minDurationId');

test('throws exception for zero discount value', function () {
    DiscountRule::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        categoryId: null,
        pricingClassId: null,
        minDays: 3,
        minDurationId: null,
        discountType: DiscountType::PERCENTAGE,
        discountValue: 0.0,
        label: 'Invalid',
    );
})->throws(DomainException::class, 'greater than 0');

test('throws exception for percentage discount over 100', function () {
    DiscountRule::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        categoryId: null,
        pricingClassId: null,
        minDays: 3,
        minDurationId: null,
        discountType: DiscountType::PERCENTAGE,
        discountValue: 150.0,
        label: 'Invalid',
    );
})->throws(DomainException::class, 'cannot exceed 100%');
