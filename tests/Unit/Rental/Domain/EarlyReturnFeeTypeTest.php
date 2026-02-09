<?php

declare(strict_types=1);

use Rental\Domain\EarlyReturnFeeType;

test('early return fee type has correct values', function () {
    expect(EarlyReturnFeeType::PERCENTAGE->value)->toBe('percentage');
    expect(EarlyReturnFeeType::FIXED->value)->toBe('fixed');
    expect(EarlyReturnFeeType::NONE->value)->toBe('none');
});

test('early return fee type can be created from string', function () {
    expect(EarlyReturnFeeType::from('percentage'))->toBe(EarlyReturnFeeType::PERCENTAGE);
    expect(EarlyReturnFeeType::from('fixed'))->toBe(EarlyReturnFeeType::FIXED);
    expect(EarlyReturnFeeType::from('none'))->toBe(EarlyReturnFeeType::NONE);
});

test('early return fee type try from returns null for invalid value', function () {
    expect(EarlyReturnFeeType::tryFrom('invalid'))->toBeNull();
});

test('early return fee type cases returns all cases', function () {
    $cases = EarlyReturnFeeType::cases();

    expect($cases)->toHaveCount(3);
    expect($cases)->toContain(EarlyReturnFeeType::PERCENTAGE);
    expect($cases)->toContain(EarlyReturnFeeType::FIXED);
    expect($cases)->toContain(EarlyReturnFeeType::NONE);
});
