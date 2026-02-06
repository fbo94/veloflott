<?php

declare(strict_types=1);

use Fleet\Domain\PricingRate;

test('can create a pricing rate', function () {
    $rate = PricingRate::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        categoryId: 'cat-123',
        pricingClassId: 'class-123',
        durationId: 'dur-123',
        price: 50.00,
    );

    expect($rate->id())->toBe('123e4567-e89b-12d3-a456-426614174000');
    expect($rate->categoryId())->toBe('cat-123');
    expect($rate->pricingClassId())->toBe('class-123');
    expect($rate->durationId())->toBe('dur-123');
    expect($rate->price())->toBe(50.00);
    expect($rate->isActive())->toBeTrue();
    expect($rate->isDeleted())->toBeFalse();
});

test('can calculate price for multiple days', function () {
    $rate = PricingRate::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        categoryId: 'cat-123',
        pricingClassId: 'class-123',
        durationId: 'dur-123',
        price: 50.00,
    );

    expect($rate->calculatePriceForDays(1))->toBe(50.00);
    expect($rate->calculatePriceForDays(3))->toBe(150.00);
    expect($rate->calculatePriceForDays(7))->toBe(350.00);
});

test('can update price', function () {
    $rate = PricingRate::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        categoryId: 'cat-123',
        pricingClassId: 'class-123',
        durationId: 'dur-123',
        price: 50.00,
    );

    $rate->updatePrice(75.00);
    expect($rate->price())->toBe(75.00);
});

test('can activate and deactivate pricing rate', function () {
    $rate = PricingRate::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        categoryId: 'cat-123',
        pricingClassId: 'class-123',
        durationId: 'dur-123',
        price: 50.00,
    );

    $rate->deactivate();
    expect($rate->isActive())->toBeFalse();

    $rate->activate();
    expect($rate->isActive())->toBeTrue();
});

test('throws exception for zero price', function () {
    PricingRate::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        categoryId: 'cat-123',
        pricingClassId: 'class-123',
        durationId: 'dur-123',
        price: 0.00,
    );
})->throws(DomainException::class, 'greater than 0');

test('throws exception for negative price', function () {
    PricingRate::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        categoryId: 'cat-123',
        pricingClassId: 'class-123',
        durationId: 'dur-123',
        price: -10.00,
    );
})->throws(DomainException::class, 'greater than 0');

test('throws exception for zero days in calculation', function () {
    $rate = PricingRate::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        categoryId: 'cat-123',
        pricingClassId: 'class-123',
        durationId: 'dur-123',
        price: 50.00,
    );

    $rate->calculatePriceForDays(0);
})->throws(DomainException::class, 'at least 1');

test('throws exception for negative days in calculation', function () {
    $rate = PricingRate::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        categoryId: 'cat-123',
        pricingClassId: 'class-123',
        durationId: 'dur-123',
        price: 50.00,
    );

    $rate->calculatePriceForDays(-1);
})->throws(DomainException::class, 'at least 1');
