<?php

declare(strict_types=1);

namespace Tests\Unit\Maintenance\Domain;

use Maintenance\Domain\MaintenanceStatus;

describe('MaintenanceStatus', function () {
    describe('label', function () {
        it('returns correct label for todo', function () {
            expect(MaintenanceStatus::TODO->label())->toBe('À faire');
        });

        it('returns correct label for in_progress', function () {
            expect(MaintenanceStatus::IN_PROGRESS->label())->toBe('En cours');
        });

        it('returns correct label for completed', function () {
            expect(MaintenanceStatus::COMPLETED->label())->toBe('Terminé');
        });
    });

    describe('canBeStarted', function () {
        it('returns true for todo status', function () {
            expect(MaintenanceStatus::TODO->canBeStarted())->toBeTrue();
        });

        it('returns false for in_progress status', function () {
            expect(MaintenanceStatus::IN_PROGRESS->canBeStarted())->toBeFalse();
        });

        it('returns false for completed status', function () {
            expect(MaintenanceStatus::COMPLETED->canBeStarted())->toBeFalse();
        });
    });

    describe('canBeCompleted', function () {
        it('returns true for todo status', function () {
            expect(MaintenanceStatus::TODO->canBeCompleted())->toBeTrue();
        });

        it('returns true for in_progress status', function () {
            expect(MaintenanceStatus::IN_PROGRESS->canBeCompleted())->toBeTrue();
        });

        it('returns false for completed status', function () {
            expect(MaintenanceStatus::COMPLETED->canBeCompleted())->toBeFalse();
        });
    });

    describe('isCompleted', function () {
        it('returns true for completed status', function () {
            expect(MaintenanceStatus::COMPLETED->isCompleted())->toBeTrue();
        });

        it('returns false for todo status', function () {
            expect(MaintenanceStatus::TODO->isCompleted())->toBeFalse();
        });

        it('returns false for in_progress status', function () {
            expect(MaintenanceStatus::IN_PROGRESS->isCompleted())->toBeFalse();
        });
    });

    describe('cases', function () {
        it('returns all statuses', function () {
            $cases = MaintenanceStatus::cases();

            expect($cases)->toHaveCount(3);
            expect($cases)->toContain(MaintenanceStatus::TODO);
            expect($cases)->toContain(MaintenanceStatus::IN_PROGRESS);
            expect($cases)->toContain(MaintenanceStatus::COMPLETED);
        });
    });

    describe('from', function () {
        it('creates status from valid string', function () {
            expect(MaintenanceStatus::from('todo'))->toBe(MaintenanceStatus::TODO);
            expect(MaintenanceStatus::from('in_progress'))->toBe(MaintenanceStatus::IN_PROGRESS);
            expect(MaintenanceStatus::from('completed'))->toBe(MaintenanceStatus::COMPLETED);
        });

        it('throws exception for invalid string', function () {
            expect(fn () => MaintenanceStatus::from('invalid'))->toThrow(\ValueError::class);
        });
    });
});
