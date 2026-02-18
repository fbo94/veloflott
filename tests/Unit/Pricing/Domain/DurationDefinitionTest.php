<?php

declare(strict_types=1);

namespace Tests\Unit\Pricing\Domain;

use Pricing\Domain\DurationDefinition;

describe('DurationDefinition', function () {
    describe('create', function () {
        it('creates a duration with hours', function () {
            $duration = DurationDefinition::create(
                id: 'dur-123',
                code: 'half_day',
                label: 'Demi-journée',
                durationHours: 4,
            );

            expect($duration->id())->toBe('dur-123');
            expect($duration->code())->toBe('half_day');
            expect($duration->label())->toBe('Demi-journée');
            expect($duration->durationHours())->toBe(4);
            expect($duration->durationDays())->toBeNull();
            expect($duration->isCustom())->toBeFalse();
            expect($duration->isActive())->toBeTrue();
        });

        it('creates a duration with days', function () {
            $duration = DurationDefinition::create(
                id: 'dur-456',
                code: 'week',
                label: 'Semaine',
                durationDays: 7,
            );

            expect($duration->durationDays())->toBe(7);
            expect($duration->durationHours())->toBeNull();
        });

        it('creates a custom duration', function () {
            $duration = DurationDefinition::create(
                id: 'dur-789',
                code: 'custom',
                label: 'Sur mesure',
                isCustom: true,
            );

            expect($duration->isCustom())->toBeTrue();
            expect($duration->durationHours())->toBeNull();
            expect($duration->durationDays())->toBeNull();
        });
    });

    describe('validation', function () {
        it('throws exception for empty code', function () {
            expect(fn () => DurationDefinition::create(
                id: 'dur-123',
                code: '',
                label: 'Day',
                durationDays: 1,
            ))->toThrow(\DomainException::class, 'Duration code cannot be empty');
        });

        it('throws exception for invalid code format', function () {
            expect(fn () => DurationDefinition::create(
                id: 'dur-123',
                code: 'Half-Day',
                label: 'Half Day',
                durationHours: 4,
            ))->toThrow(\DomainException::class, 'Duration code must contain only lowercase letters, numbers and underscores');
        });

        it('throws exception for empty label', function () {
            expect(fn () => DurationDefinition::create(
                id: 'dur-123',
                code: 'day',
                label: '',
                durationDays: 1,
            ))->toThrow(\DomainException::class, 'Duration label cannot be empty');
        });

        it('throws exception when no duration specified and not custom', function () {
            expect(fn () => DurationDefinition::create(
                id: 'dur-123',
                code: 'day',
                label: 'Day',
            ))->toThrow(\DomainException::class, 'Duration must have either hours or days specified');
        });

        it('throws exception for duration hours less than 1', function () {
            expect(fn () => DurationDefinition::create(
                id: 'dur-123',
                code: 'day',
                label: 'Day',
                durationHours: 0,
            ))->toThrow(\DomainException::class, 'Duration hours must be at least 1');
        });

        it('throws exception for duration days less than 1', function () {
            expect(fn () => DurationDefinition::create(
                id: 'dur-123',
                code: 'day',
                label: 'Day',
                durationDays: 0,
            ))->toThrow(\DomainException::class, 'Duration days must be at least 1');
        });
    });

    describe('totalHours', function () {
        it('returns hours directly when set', function () {
            $duration = DurationDefinition::create(
                id: 'dur-123',
                code: 'half_day',
                label: 'Demi-journée',
                durationHours: 4,
            );

            expect($duration->totalHours())->toBe(4);
        });

        it('calculates hours from days', function () {
            $duration = DurationDefinition::create(
                id: 'dur-123',
                code: 'week',
                label: 'Semaine',
                durationDays: 7,
            );

            expect($duration->totalHours())->toBe(168);
        });

        it('returns null for custom duration', function () {
            $duration = DurationDefinition::create(
                id: 'dur-123',
                code: 'custom',
                label: 'Custom',
                isCustom: true,
            );

            expect($duration->totalHours())->toBeNull();
        });
    });

    describe('approximateDays', function () {
        it('returns days directly when set', function () {
            $duration = DurationDefinition::create(
                id: 'dur-123',
                code: 'week',
                label: 'Semaine',
                durationDays: 7,
            );

            expect($duration->approximateDays())->toBe(7.0);
        });

        it('calculates days from hours', function () {
            $duration = DurationDefinition::create(
                id: 'dur-123',
                code: 'half_day',
                label: 'Demi-journée',
                durationHours: 12,
            );

            expect($duration->approximateDays())->toBe(0.5);
        });

        it('returns null for custom duration', function () {
            $duration = DurationDefinition::create(
                id: 'dur-123',
                code: 'custom',
                label: 'Custom',
                isCustom: true,
            );

            expect($duration->approximateDays())->toBeNull();
        });
    });

    describe('activate', function () {
        it('activates an inactive duration', function () {
            $duration = DurationDefinition::create(
                id: 'dur-123',
                code: 'day',
                label: 'Journée',
                durationDays: 1,
            );
            $duration->deactivate();

            $duration->activate();

            expect($duration->isActive())->toBeTrue();
        });

        it('throws exception when already active', function () {
            $duration = DurationDefinition::create(
                id: 'dur-123',
                code: 'day',
                label: 'Journée',
                durationDays: 1,
            );

            expect(fn () => $duration->activate())
                ->toThrow(\DomainException::class, 'Duration is already active');
        });
    });

    describe('deactivate', function () {
        it('deactivates an active duration', function () {
            $duration = DurationDefinition::create(
                id: 'dur-123',
                code: 'day',
                label: 'Journée',
                durationDays: 1,
            );

            $duration->deactivate();

            expect($duration->isActive())->toBeFalse();
        });

        it('throws exception when already inactive', function () {
            $duration = DurationDefinition::create(
                id: 'dur-123',
                code: 'day',
                label: 'Journée',
                durationDays: 1,
            );
            $duration->deactivate();

            expect(fn () => $duration->deactivate())
                ->toThrow(\DomainException::class, 'Duration is already inactive');
        });
    });

    describe('update', function () {
        it('updates duration properties', function () {
            $duration = DurationDefinition::create(
                id: 'dur-123',
                code: 'day',
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
    });
});
