<?php

declare(strict_types=1);

use Fleet\Domain\DurationDefinition;

test('can create a duration with days', function () {
    $duration = DurationDefinition::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        code: 'full_day',
        label: 'Journée',
        durationHours: null,
        durationDays: 1,
        isCustom: false,
        sortOrder: 1,
    );

    expect($duration->id())->toBe('123e4567-e89b-12d3-a456-426614174000');
    expect($duration->code())->toBe('full_day');
    expect($duration->label())->toBe('Journée');
    expect($duration->durationHours())->toBeNull();
    expect($duration->durationDays())->toBe(1);
    expect($duration->isCustom())->toBeFalse();
    expect($duration->isActive())->toBeTrue();
});

test('can create a duration with hours', function () {
    $duration = DurationDefinition::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        code: 'half_day',
        label: 'Demi-journée',
        durationHours: 4,
        durationDays: null,
    );

    expect($duration->durationHours())->toBe(4);
    expect($duration->durationDays())->toBeNull();
});

test('can calculate total hours from days', function () {
    $duration = DurationDefinition::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        code: 'week',
        label: 'Semaine',
        durationDays: 7,
    );

    expect($duration->totalHours())->toBe(168); // 7 * 24
});

test('can calculate total hours from hours', function () {
    $duration = DurationDefinition::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        code: 'half_day',
        label: 'Demi-journée',
        durationHours: 4,
    );

    expect($duration->totalHours())->toBe(4);
});

test('can calculate approximate days', function () {
    $duration = DurationDefinition::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        code: 'half_day',
        label: 'Demi-journée',
        durationHours: 12,
    );

    expect($duration->approximateDays())->toBe(0.5);
});

test('can update duration', function () {
    $duration = DurationDefinition::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        code: 'full_day',
        label: 'Journée',
        durationDays: 1,
    );

    $duration->update(
        label: 'Journée complète',
        durationHours: null,
        durationDays: 2,
        sortOrder: 5,
    );

    expect($duration->label())->toBe('Journée complète');
    expect($duration->durationDays())->toBe(2);
    expect($duration->sortOrder())->toBe(5);
});

test('can activate and deactivate duration', function () {
    $duration = DurationDefinition::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        code: 'full_day',
        label: 'Journée',
        durationDays: 1,
    );

    $duration->deactivate();
    expect($duration->isActive())->toBeFalse();

    $duration->activate();
    expect($duration->isActive())->toBeTrue();
});

test('throws exception when neither hours nor days are specified', function () {
    DurationDefinition::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        code: 'invalid',
        label: 'Invalid',
        durationHours: null,
        durationDays: null,
        isCustom: false,  // Not custom, so duration required
    );
})->throws(DomainException::class, 'hours or days');

test('allows custom duration without hours or days', function () {
    $duration = DurationDefinition::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        code: 'custom',
        label: 'Durée personnalisée',
        durationHours: null,
        durationDays: null,
        isCustom: true,  // Custom duration can be without specific duration
    );

    expect($duration->isCustom())->toBeTrue();
    expect($duration->totalHours())->toBeNull();
});

test('validates code format', function () {
    DurationDefinition::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        code: 'Invalid Code!',
        label: 'Invalid',
        durationDays: 1,
    );
})->throws(DomainException::class, 'lowercase letters, numbers and underscores');

test('validates minimum hours', function () {
    DurationDefinition::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        code: 'invalid',
        label: 'Invalid',
        durationHours: 0,
    );
})->throws(DomainException::class, 'at least 1');

test('validates minimum days', function () {
    DurationDefinition::create(
        id: '123e4567-e89b-12d3-a456-426614174000',
        code: 'invalid',
        label: 'Invalid',
        durationDays: 0,
    );
})->throws(DomainException::class, 'at least 1');
