<?php

declare(strict_types=1);

namespace Tests\Unit\Fleet\Domain;

use Fleet\Domain\BrakeType;

describe('BrakeType', function () {
    describe('label', function () {
        it('returns correct label for hydraulic disc', function () {
            expect(BrakeType::HYDRAULIC_DISC->label())->toBe('Disque hydraulique');
        });

        it('returns correct label for mechanical disc', function () {
            expect(BrakeType::MECHANICAL_DISC->label())->toBe('Disque mécanique');
        });

        it('returns correct label for mechanical rim', function () {
            expect(BrakeType::MECHANICAL_RIM->label())->toBe('Jante mécanique');
        });

        it('returns correct label for hydraulic rim', function () {
            expect(BrakeType::HYDRAULIC_RIM->label())->toBe('Jante hydraulique');
        });

        it('returns correct label for drum', function () {
            expect(BrakeType::DRUM->label())->toBe('Tambour');
        });
    });

    describe('cases', function () {
        it('returns all brake types', function () {
            $cases = BrakeType::cases();

            expect($cases)->toHaveCount(6);
            expect($cases)->toContain(BrakeType::HYDRAULIC_DISC);
            expect($cases)->toContain(BrakeType::MECHANICAL_DISC);
            expect($cases)->toContain(BrakeType::NONE);
        });
    });

    describe('from', function () {
        it('creates brake type from valid string', function () {
            expect(BrakeType::from('hydraulic_disc'))->toBe(BrakeType::HYDRAULIC_DISC);
            expect(BrakeType::from('mechanical_disc'))->toBe(BrakeType::MECHANICAL_DISC);
        });

        it('throws exception for invalid string', function () {
            expect(fn () => BrakeType::from('invalid'))->toThrow(\ValueError::class);
        });
    });
});
