<?php

declare(strict_types=1);

namespace Tests\Unit\Fleet\Domain;

use Fleet\Domain\BikeStatus;

describe('BikeStatus', function () {
    describe('label', function () {
        it('returns correct label for available', function () {
            expect(BikeStatus::AVAILABLE->label())->toBe('Disponible');
        });

        it('returns correct label for rented', function () {
            expect(BikeStatus::RENTED->label())->toBe('En location');
        });

        it('returns correct label for maintenance', function () {
            expect(BikeStatus::MAINTENANCE->label())->toBe('En maintenance');
        });

        it('returns correct label for unavailable', function () {
            expect(BikeStatus::UNAVAILABLE->label())->toBe('Indisponible');
        });

        it('returns correct label for retired', function () {
            expect(BikeStatus::RETIRED->label())->toBe('Retiré');
        });
    });

    describe('isRentable', function () {
        it('returns true only for available status', function () {
            expect(BikeStatus::AVAILABLE->isRentable())->toBeTrue();
            expect(BikeStatus::RENTED->isRentable())->toBeFalse();
            expect(BikeStatus::MAINTENANCE->isRentable())->toBeFalse();
            expect(BikeStatus::UNAVAILABLE->isRentable())->toBeFalse();
            expect(BikeStatus::RETIRED->isRentable())->toBeFalse();
        });
    });

    describe('canBeModified', function () {
        it('returns true for non-rented statuses', function () {
            expect(BikeStatus::AVAILABLE->canBeModified())->toBeTrue();
            expect(BikeStatus::MAINTENANCE->canBeModified())->toBeTrue();
            expect(BikeStatus::UNAVAILABLE->canBeModified())->toBeTrue();
            expect(BikeStatus::RETIRED->canBeModified())->toBeTrue();
        });

        it('returns false for rented status', function () {
            expect(BikeStatus::RENTED->canBeModified())->toBeFalse();
        });
    });

    describe('cases', function () {
        it('returns all statuses', function () {
            $cases = BikeStatus::cases();

            expect($cases)->toHaveCount(5);
            expect($cases)->toContain(BikeStatus::AVAILABLE);
            expect($cases)->toContain(BikeStatus::RENTED);
            expect($cases)->toContain(BikeStatus::MAINTENANCE);
            expect($cases)->toContain(BikeStatus::UNAVAILABLE);
            expect($cases)->toContain(BikeStatus::RETIRED);
        });
    });

    describe('from', function () {
        it('creates status from valid string', function () {
            expect(BikeStatus::from('available'))->toBe(BikeStatus::AVAILABLE);
            expect(BikeStatus::from('rented'))->toBe(BikeStatus::RENTED);
            expect(BikeStatus::from('maintenance'))->toBe(BikeStatus::MAINTENANCE);
            expect(BikeStatus::from('unavailable'))->toBe(BikeStatus::UNAVAILABLE);
            expect(BikeStatus::from('retired'))->toBe(BikeStatus::RETIRED);
        });

        it('throws exception for invalid string', function () {
            expect(fn () => BikeStatus::from('invalid'))->toThrow(\ValueError::class);
        });
    });
});
