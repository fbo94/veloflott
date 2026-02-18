<?php

declare(strict_types=1);

namespace Tests\Unit\Rental\Domain;

use Rental\Domain\RentalSettingsId;

describe('RentalSettingsId', function () {
    describe('fromString', function () {
        it('creates an id from string', function () {
            $id = RentalSettingsId::fromString('test-id-123');

            expect($id->value())->toBe('test-id-123');
        });
    });

    describe('generate', function () {
        it('generates a unique uuid', function () {
            $id1 = RentalSettingsId::generate();
            $id2 = RentalSettingsId::generate();

            expect($id1->value())->not->toBe($id2->value());
            expect(strlen($id1->value()))->toBe(36);
        });
    });

    describe('equals', function () {
        it('returns true for equal ids', function () {
            $id1 = RentalSettingsId::fromString('same-id');
            $id2 = RentalSettingsId::fromString('same-id');

            expect($id1->equals($id2))->toBeTrue();
        });

        it('returns false for different ids', function () {
            $id1 = RentalSettingsId::fromString('id-1');
            $id2 = RentalSettingsId::fromString('id-2');

            expect($id1->equals($id2))->toBeFalse();
        });
    });

    describe('__toString', function () {
        it('converts to string', function () {
            $id = RentalSettingsId::fromString('test-id');

            expect((string) $id)->toBe('test-id');
        });
    });
});
