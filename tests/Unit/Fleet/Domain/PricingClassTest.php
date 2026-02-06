<?php

declare(strict_types=1);

use Fleet\Domain\PricingClass;

test('can create a pricing class', function () {
    $pricingClass = PricingClass::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        code: 'premium',
        label: 'Premium',
        description: 'Premium bikes',
        color: '#8B5CF6',
        sortOrder: 1,
    );

    expect($pricingClass->id())->toBe('123e4567-e89b-12d3-a456-426614174000');
    expect($pricingClass->code())->toBe('premium');
    expect($pricingClass->label())->toBe('Premium');
    expect($pricingClass->description())->toBe('Premium bikes');
    expect($pricingClass->color())->toBe('#8B5CF6');
    expect($pricingClass->sortOrder())->toBe(1);
    expect($pricingClass->isActive())->toBeTrue();
    expect($pricingClass->isDeleted())->toBeFalse();
});

test('can update pricing class', function () {
    $pricingClass = PricingClass::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        code: 'premium',
        label: 'Premium',
    );

    $pricingClass->update(
        label: 'Premium Plus',
        description: 'Enhanced premium bikes',
        color: '#EF4444',
        sortOrder: 2,
    );

    expect($pricingClass->label())->toBe('Premium Plus');
    expect($pricingClass->description())->toBe('Enhanced premium bikes');
    expect($pricingClass->color())->toBe('#EF4444');
    expect($pricingClass->sortOrder())->toBe(2);
});

test('can activate and deactivate pricing class', function () {
    $pricingClass = PricingClass::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        code: 'premium',
        label: 'Premium',
    );

    expect($pricingClass->isActive())->toBeTrue();

    $pricingClass->deactivate();
    expect($pricingClass->isActive())->toBeFalse();

    $pricingClass->activate();
    expect($pricingClass->isActive())->toBeTrue();
});

test('throws exception when activating already active pricing class', function () {
    $pricingClass = PricingClass::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        code: 'premium',
        label: 'Premium',
    );

    $pricingClass->activate();
})->throws(DomainException::class, 'already active');

test('throws exception when deactivating already inactive pricing class', function () {
    $pricingClass = PricingClass::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        code: 'premium',
        label: 'Premium',
    );

    $pricingClass->deactivate();
    $pricingClass->deactivate();
})->throws(DomainException::class, 'already inactive');

test('validates code format', function () {
    PricingClass::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        code: 'Invalid Code!',  // Invalid: uppercase and special chars
        label: 'Invalid',
    );
})->throws(DomainException::class, 'lowercase letters, numbers and underscores');

test('validates empty code', function () {
    PricingClass::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        code: '',
        label: 'Test',
    );
})->throws(DomainException::class, 'cannot be empty');

test('validates empty label', function () {
    PricingClass::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        code: 'test',
        label: '',
    );
})->throws(DomainException::class, 'cannot be empty');

test('validates color format', function () {
    PricingClass::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        code: 'premium',
        label: 'Premium',
        color: 'invalid-color',
    );
})->throws(DomainException::class, 'valid hex color code');

test('accepts valid hex color', function () {
    $pricingClass = PricingClass::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        code: 'premium',
        label: 'Premium',
        color: '#FF5733',
    );

    expect($pricingClass->color())->toBe('#FF5733');
});

test('accepts null color', function () {
    $pricingClass = PricingClass::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        code: 'premium',
        label: 'Premium',
        color: null,
    );

    expect($pricingClass->color())->toBeNull();
});
