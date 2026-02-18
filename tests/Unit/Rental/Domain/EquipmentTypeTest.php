<?php

declare(strict_types=1);

namespace Tests\Unit\Rental\Domain;

use Rental\Domain\EquipmentType;

describe('EquipmentType', function () {
    describe('label', function () {
        it('returns correct label for helmet', function () {
            expect(EquipmentType::HELMET->label())->toBe('Casque');
        });

        it('returns correct label for knee pads', function () {
            expect(EquipmentType::KNEE_PADS->label())->toBe('Genouillères');
        });

        it('returns correct label for elbow pads', function () {
            expect(EquipmentType::ELBOW_PADS->label())->toBe('Coudières');
        });

        it('returns correct label for gloves', function () {
            expect(EquipmentType::GLOVES->label())->toBe('Gants');
        });

        it('returns correct label for backpack', function () {
            expect(EquipmentType::BACKPACK->label())->toBe('Sac à dos');
        });

        it('returns correct label for lock', function () {
            expect(EquipmentType::LOCK->label())->toBe('Antivol');
        });

        it('returns correct label for other', function () {
            expect(EquipmentType::OTHER->label())->toBe('Autre');
        });
    });

    describe('defaultPrice', function () {
        it('returns correct default price for helmet', function () {
            expect(EquipmentType::HELMET->defaultPrice())->toBe(5.0);
        });

        it('returns correct default price for knee pads', function () {
            expect(EquipmentType::KNEE_PADS->defaultPrice())->toBe(3.0);
        });

        it('returns correct default price for gloves', function () {
            expect(EquipmentType::GLOVES->defaultPrice())->toBe(2.0);
        });

        it('returns correct default price for lock', function () {
            expect(EquipmentType::LOCK->defaultPrice())->toBe(2.0);
        });

        it('returns zero for other', function () {
            expect(EquipmentType::OTHER->defaultPrice())->toBe(0.0);
        });
    });

    describe('cases', function () {
        it('returns all equipment types', function () {
            $cases = EquipmentType::cases();

            expect($cases)->toHaveCount(7);
            expect($cases)->toContain(EquipmentType::HELMET);
            expect($cases)->toContain(EquipmentType::KNEE_PADS);
            expect($cases)->toContain(EquipmentType::LOCK);
        });
    });

    describe('from', function () {
        it('creates equipment type from valid string', function () {
            expect(EquipmentType::from('helmet'))->toBe(EquipmentType::HELMET);
            expect(EquipmentType::from('knee_pads'))->toBe(EquipmentType::KNEE_PADS);
            expect(EquipmentType::from('lock'))->toBe(EquipmentType::LOCK);
        });

        it('throws exception for invalid string', function () {
            expect(fn () => EquipmentType::from('invalid'))->toThrow(\ValueError::class);
        });
    });
});
