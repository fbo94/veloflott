<?php

declare(strict_types=1);

namespace Tests\Unit\Maintenance\Domain;

use DateTimeImmutable;
use Maintenance\Domain\Exceptions\MaintenanceException;
use Maintenance\Domain\Maintenance;
use Maintenance\Domain\MaintenancePriority;
use Maintenance\Domain\MaintenanceReason;
use Maintenance\Domain\MaintenanceStatus;
use Maintenance\Domain\MaintenanceType;

describe('Maintenance', function () {
    function createMaintenance(array $overrides = []): Maintenance
    {
        return Maintenance::declare(
            id: $overrides['id'] ?? 'maint-123',
            bikeId: $overrides['bikeId'] ?? 'bike-456',
            type: $overrides['type'] ?? MaintenanceType::CURATIVE,
            reason: $overrides['reason'] ?? MaintenanceReason::INNER_TUBE_REPLACEMENT,
            priority: $overrides['priority'] ?? MaintenancePriority::NORMAL,
            description: array_key_exists('description', $overrides) ? $overrides['description'] : 'Test description',
            scheduledAt: array_key_exists('scheduledAt', $overrides) ? $overrides['scheduledAt'] : null,
            photos: $overrides['photos'] ?? [],
        );
    }

    describe('declare', function () {
        it('creates a new maintenance with todo status', function () {
            $maintenance = createMaintenance();

            expect($maintenance->id())->toBe('maint-123');
            expect($maintenance->bikeId())->toBe('bike-456');
            expect($maintenance->type())->toBe(MaintenanceType::CURATIVE);
            expect($maintenance->reason())->toBe(MaintenanceReason::INNER_TUBE_REPLACEMENT);
            expect($maintenance->priority())->toBe(MaintenancePriority::NORMAL);
            expect($maintenance->status())->toBe(MaintenanceStatus::TODO);
            expect($maintenance->description())->toBe('Test description');
        });

        it('sets timestamps on creation', function () {
            $maintenance = createMaintenance();

            expect($maintenance->createdAt())->toBeInstanceOf(DateTimeImmutable::class);
            expect($maintenance->updatedAt())->toBeInstanceOf(DateTimeImmutable::class);
            expect($maintenance->scheduledAt())->toBeInstanceOf(DateTimeImmutable::class);
        });

        it('uses provided scheduled date', function () {
            $scheduledAt = new DateTimeImmutable('2024-06-15');
            $maintenance = createMaintenance(['scheduledAt' => $scheduledAt]);

            expect($maintenance->scheduledAt())->toBe($scheduledAt);
        });

        it('starts with null work fields', function () {
            $maintenance = createMaintenance();

            expect($maintenance->startedAt())->toBeNull();
            expect($maintenance->completedAt())->toBeNull();
            expect($maintenance->workDescription())->toBeNull();
            expect($maintenance->partsReplaced())->toBeNull();
            expect($maintenance->cost())->toBeNull();
        });

        it('accepts photos on creation', function () {
            $photos = ['photo1.jpg', 'photo2.jpg'];
            $maintenance = createMaintenance(['photos' => $photos]);

            expect($maintenance->photos())->toBe($photos);
        });
    });

    describe('start', function () {
        it('changes status to in_progress', function () {
            $maintenance = createMaintenance();

            $maintenance->start();

            expect($maintenance->status())->toBe(MaintenanceStatus::IN_PROGRESS);
        });

        it('sets started_at timestamp', function () {
            $maintenance = createMaintenance();

            $maintenance->start();

            expect($maintenance->startedAt())->toBeInstanceOf(DateTimeImmutable::class);
        });

        it('throws exception when already in progress', function () {
            $maintenance = createMaintenance();
            $maintenance->start();

            expect(fn () => $maintenance->start())->toThrow(MaintenanceException::class);
        });

        it('throws exception when completed', function () {
            $maintenance = createMaintenance();
            $maintenance->complete('Work done');

            expect(fn () => $maintenance->start())->toThrow(MaintenanceException::class);
        });
    });

    describe('complete', function () {
        it('changes status to completed from todo', function () {
            $maintenance = createMaintenance();

            $maintenance->complete('Fixed the flat tire');

            expect($maintenance->status())->toBe(MaintenanceStatus::COMPLETED);
        });

        it('changes status to completed from in_progress', function () {
            $maintenance = createMaintenance();
            $maintenance->start();

            $maintenance->complete('Fixed the flat tire');

            expect($maintenance->status())->toBe(MaintenanceStatus::COMPLETED);
        });

        it('sets completed_at timestamp', function () {
            $maintenance = createMaintenance();

            $maintenance->complete('Work done');

            expect($maintenance->completedAt())->toBeInstanceOf(DateTimeImmutable::class);
        });

        it('sets work description', function () {
            $maintenance = createMaintenance();

            $maintenance->complete('Replaced inner tube');

            expect($maintenance->workDescription())->toBe('Replaced inner tube');
        });

        it('sets parts replaced when provided', function () {
            $maintenance = createMaintenance();

            $maintenance->complete('Fixed', 'Inner tube, tire sealant');

            expect($maintenance->partsReplaced())->toBe('Inner tube, tire sealant');
        });

        it('sets cost when provided', function () {
            $maintenance = createMaintenance();

            $maintenance->complete('Fixed', null, 2500); // 25.00 EUR in centimes

            expect($maintenance->cost())->toBe(2500);
        });

        it('throws exception when already completed', function () {
            $maintenance = createMaintenance();
            $maintenance->complete('Work done');

            expect(fn () => $maintenance->complete('More work'))->toThrow(MaintenanceException::class);
        });
    });

    describe('updateDescription', function () {
        it('updates the description', function () {
            $maintenance = createMaintenance(['description' => 'Original']);

            $maintenance->updateDescription('Updated description');

            expect($maintenance->description())->toBe('Updated description');
        });

        it('throws exception when completed', function () {
            $maintenance = createMaintenance();
            $maintenance->complete('Work done');

            expect(fn () => $maintenance->updateDescription('New desc'))->toThrow(MaintenanceException::class);
        });

        it('can update when in progress', function () {
            $maintenance = createMaintenance();
            $maintenance->start();

            $maintenance->updateDescription('Updated while in progress');

            expect($maintenance->description())->toBe('Updated while in progress');
        });
    });

    describe('photos', function () {
        it('can add a photo', function () {
            $maintenance = createMaintenance(['photos' => []]);

            $maintenance->addPhoto('new-photo.jpg');

            expect($maintenance->photos())->toContain('new-photo.jpg');
        });

        it('returns self for method chaining', function () {
            $maintenance = createMaintenance();

            $result = $maintenance->addPhoto('photo.jpg');

            expect($result)->toBe($maintenance);
        });

        it('can remove a photo', function () {
            $maintenance = createMaintenance(['photos' => ['photo1.jpg', 'photo2.jpg']]);

            $maintenance->removePhoto('photo1.jpg');

            expect($maintenance->photos())->not->toContain('photo1.jpg');
            expect($maintenance->photos())->toContain('photo2.jpg');
        });

        it('removePhoto returns self for method chaining', function () {
            $maintenance = createMaintenance(['photos' => ['photo.jpg']]);

            $result = $maintenance->removePhoto('photo.jpg');

            expect($result)->toBe($maintenance);
        });

        it('removing non-existent photo does nothing', function () {
            $photos = ['photo1.jpg'];
            $maintenance = createMaintenance(['photos' => $photos]);

            $maintenance->removePhoto('non-existent.jpg');

            expect($maintenance->photos())->toHaveCount(1);
        });
    });

    describe('reconstitute', function () {
        it('creates maintenance with all fields', function () {
            $now = new DateTimeImmutable();
            $startedAt = new DateTimeImmutable('-1 day');
            $completedAt = new DateTimeImmutable();

            $maintenance = Maintenance::reconstitute(
                id: 'maint-999',
                bikeId: 'bike-888',
                type: MaintenanceType::PREVENTIVE,
                reason: MaintenanceReason::BRAKE_PAD_REPLACEMENT,
                priority: MaintenancePriority::URGENT,
                status: MaintenanceStatus::COMPLETED,
                description: 'Full service',
                scheduledAt: $now,
                startedAt: $startedAt,
                completedAt: $completedAt,
                workDescription: 'Completed full service',
                partsReplaced: 'Brake pads',
                cost: 5000,
                photos: ['before.jpg', 'after.jpg'],
                createdAt: $now,
                updatedAt: $now,
            );

            expect($maintenance->id())->toBe('maint-999');
            expect($maintenance->bikeId())->toBe('bike-888');
            expect($maintenance->type())->toBe(MaintenanceType::PREVENTIVE);
            expect($maintenance->reason())->toBe(MaintenanceReason::BRAKE_PAD_REPLACEMENT);
            expect($maintenance->priority())->toBe(MaintenancePriority::URGENT);
            expect($maintenance->status())->toBe(MaintenanceStatus::COMPLETED);
            expect($maintenance->description())->toBe('Full service');
            expect($maintenance->startedAt())->toBe($startedAt);
            expect($maintenance->completedAt())->toBe($completedAt);
            expect($maintenance->workDescription())->toBe('Completed full service');
            expect($maintenance->partsReplaced())->toBe('Brake pads');
            expect($maintenance->cost())->toBe(5000);
            expect($maintenance->photos())->toHaveCount(2);
        });
    });
});
