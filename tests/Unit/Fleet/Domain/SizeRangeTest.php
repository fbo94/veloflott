<?php

declare(strict_types=1);

use Fleet\Domain\SizeRange;

test('can create a size range with min and max', function () {
    // Arrange & Act
    $range = new SizeRange(min: 48, max: 50);

    // Assert
    expect($range->min())->toBe(48);
    expect($range->max())->toBe(50);
});

test('throws exception when min is greater than max', function () {
    // Act & Assert
    expect(fn () => new SizeRange(min: 60, max: 50))
        ->toThrow(\DomainException::class, 'Minimum value cannot be greater than maximum value');
});

test('throws exception when min is negative', function () {
    // Act & Assert
    expect(fn () => new SizeRange(min: -5, max: 10))
        ->toThrow(\DomainException::class, 'Size range values must be positive');
});

test('throws exception when max is negative', function () {
    // Act & Assert
    expect(fn () => new SizeRange(min: 5, max: -10))
        ->toThrow(\DomainException::class, 'Size range values must be positive');
});

test('can create a range with same min and max', function () {
    // Arrange & Act
    $range = new SizeRange(min: 50, max: 50);

    // Assert
    expect($range->min())->toBe(50);
    expect($range->max())->toBe(50);
});

test('can check if value is within range', function () {
    // Arrange
    $range = new SizeRange(min: 48, max: 50);

    // Act & Assert
    expect($range->contains(48))->toBeTrue();
    expect($range->contains(49))->toBeTrue();
    expect($range->contains(50))->toBeTrue();
    expect($range->contains(47))->toBeFalse();
    expect($range->contains(51))->toBeFalse();
});

test('converts to array correctly', function () {
    // Arrange
    $range = new SizeRange(min: 48, max: 50);

    // Act
    $array = $range->toArray();

    // Assert
    expect($array)->toBe(['min' => 48, 'max' => 50]);
});
