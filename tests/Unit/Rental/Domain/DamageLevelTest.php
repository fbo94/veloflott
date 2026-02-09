<?php

declare(strict_types=1);

use Rental\Domain\DamageLevel;

test('damage level has correct values', function () {
    expect(DamageLevel::NONE->value)->toBe('none');
    expect(DamageLevel::MINOR->value)->toBe('minor');
    expect(DamageLevel::MAJOR->value)->toBe('major');
    expect(DamageLevel::TOTAL_LOSS->value)->toBe('total_loss');
});

test('damage level can be created from string', function () {
    expect(DamageLevel::from('none'))->toBe(DamageLevel::NONE);
    expect(DamageLevel::from('minor'))->toBe(DamageLevel::MINOR);
    expect(DamageLevel::from('major'))->toBe(DamageLevel::MAJOR);
    expect(DamageLevel::from('total_loss'))->toBe(DamageLevel::TOTAL_LOSS);
});

test('damage level try from returns null for invalid value', function () {
    expect(DamageLevel::tryFrom('invalid'))->toBeNull();
});

test('damage level cases returns all cases', function () {
    $cases = DamageLevel::cases();

    expect($cases)->toHaveCount(4);
    expect($cases)->toContain(DamageLevel::NONE);
    expect($cases)->toContain(DamageLevel::MINOR);
    expect($cases)->toContain(DamageLevel::MAJOR);
    expect($cases)->toContain(DamageLevel::TOTAL_LOSS);
});
