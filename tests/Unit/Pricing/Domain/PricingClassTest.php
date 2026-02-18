<?php

declare(strict_types=1);

namespace Tests\Unit\Pricing\Domain;

use Pricing\Domain\PricingClass;

describe('PricingClass', function () {
    describe('create', function () {
        it('creates a pricing class with required fields', function () {
            $pricingClass = PricingClass::create(
                id: 'class-123',
                code: 'premium',
                label: 'Premium',
            );

            expect($pricingClass->id())->toBe('class-123');
            expect($pricingClass->code())->toBe('premium');
            expect($pricingClass->label())->toBe('Premium');
            expect($pricingClass->description())->toBeNull();
            expect($pricingClass->color())->toBeNull();
            expect($pricingClass->sortOrder())->toBe(0);
            expect($pricingClass->isActive())->toBeTrue();
            expect($pricingClass->isDeleted())->toBeFalse();
        });

        it('creates a pricing class with all fields', function () {
            $pricingClass = PricingClass::create(
                id: 'class-456',
                code: 'luxury',
                label: 'Luxe',
                description: 'Premium luxury bikes',
                color: '#FFD700',
                sortOrder: 10,
            );

            expect($pricingClass->description())->toBe('Premium luxury bikes');
            expect($pricingClass->color())->toBe('#FFD700');
            expect($pricingClass->sortOrder())->toBe(10);
        });
    });

    describe('validation', function () {
        it('throws exception for empty code', function () {
            expect(fn () => PricingClass::create(
                id: 'class-123',
                code: '',
                label: 'Premium',
            ))->toThrow(\DomainException::class, 'Pricing class code cannot be empty');
        });

        it('throws exception for invalid code format', function () {
            expect(fn () => PricingClass::create(
                id: 'class-123',
                code: 'Premium-Class',
                label: 'Premium',
            ))->toThrow(\DomainException::class, 'Pricing class code must contain only lowercase letters, numbers and underscores');
        });

        it('throws exception for code exceeding 50 characters', function () {
            expect(fn () => PricingClass::create(
                id: 'class-123',
                code: str_repeat('a', 51),
                label: 'Premium',
            ))->toThrow(\DomainException::class, 'Pricing class code cannot exceed 50 characters');
        });

        it('throws exception for empty label', function () {
            expect(fn () => PricingClass::create(
                id: 'class-123',
                code: 'premium',
                label: '',
            ))->toThrow(\DomainException::class, 'Pricing class label cannot be empty');
        });

        it('throws exception for label exceeding 100 characters', function () {
            expect(fn () => PricingClass::create(
                id: 'class-123',
                code: 'premium',
                label: str_repeat('a', 101),
            ))->toThrow(\DomainException::class, 'Pricing class label cannot exceed 100 characters');
        });

        it('throws exception for invalid color format', function () {
            expect(fn () => PricingClass::create(
                id: 'class-123',
                code: 'premium',
                label: 'Premium',
                color: 'red',
            ))->toThrow(\DomainException::class, 'Pricing class color must be a valid hex color code');
        });
    });

    describe('update', function () {
        it('updates pricing class properties', function () {
            $pricingClass = PricingClass::create(
                id: 'class-123',
                code: 'premium',
                label: 'Premium',
            );

            $pricingClass->update(
                label: 'Premium Plus',
                description: 'Updated description',
                color: '#0000FF',
                sortOrder: 5,
            );

            expect($pricingClass->label())->toBe('Premium Plus');
            expect($pricingClass->description())->toBe('Updated description');
            expect($pricingClass->color())->toBe('#0000FF');
            expect($pricingClass->sortOrder())->toBe(5);
        });
    });

    describe('activate', function () {
        it('activates an inactive pricing class', function () {
            $pricingClass = PricingClass::create(
                id: 'class-123',
                code: 'premium',
                label: 'Premium',
            );
            $pricingClass->deactivate();

            $pricingClass->activate();

            expect($pricingClass->isActive())->toBeTrue();
        });

        it('throws exception when already active', function () {
            $pricingClass = PricingClass::create(
                id: 'class-123',
                code: 'premium',
                label: 'Premium',
            );

            expect(fn () => $pricingClass->activate())
                ->toThrow(\DomainException::class, 'Pricing class is already active');
        });
    });

    describe('deactivate', function () {
        it('deactivates an active pricing class', function () {
            $pricingClass = PricingClass::create(
                id: 'class-123',
                code: 'premium',
                label: 'Premium',
            );

            $pricingClass->deactivate();

            expect($pricingClass->isActive())->toBeFalse();
        });

        it('throws exception when already inactive', function () {
            $pricingClass = PricingClass::create(
                id: 'class-123',
                code: 'premium',
                label: 'Premium',
            );
            $pricingClass->deactivate();

            expect(fn () => $pricingClass->deactivate())
                ->toThrow(\DomainException::class, 'Pricing class is already inactive');
        });
    });
});
