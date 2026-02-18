<?php

declare(strict_types=1);

namespace Tests\Unit\Fleet\Domain;

use Fleet\Domain\UnavailabilityReason;

describe('UnavailabilityReason', function () {
    describe('label', function () {
        it('returns correct label for reserved', function () {
            expect(UnavailabilityReason::RESERVED->label())->toBe('Réservé');
        });

        it('returns correct label for loaned', function () {
            expect(UnavailabilityReason::LOANED->label())->toBe('Prêt');
        });

        it('returns correct label for other', function () {
            expect(UnavailabilityReason::OTHER->label())->toBe('Autre');
        });
    });

    describe('cases', function () {
        it('returns all unavailability reasons', function () {
            $cases = UnavailabilityReason::cases();

            expect($cases)->toHaveCount(3);
            expect($cases)->toContain(UnavailabilityReason::RESERVED);
            expect($cases)->toContain(UnavailabilityReason::LOANED);
            expect($cases)->toContain(UnavailabilityReason::OTHER);
        });
    });

    describe('from', function () {
        it('creates reason from valid string', function () {
            expect(UnavailabilityReason::from('reserved'))->toBe(UnavailabilityReason::RESERVED);
            expect(UnavailabilityReason::from('loaned'))->toBe(UnavailabilityReason::LOANED);
        });

        it('throws exception for invalid string', function () {
            expect(fn () => UnavailabilityReason::from('invalid'))->toThrow(\ValueError::class);
        });
    });
});
