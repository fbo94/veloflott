<?php

declare(strict_types=1);

namespace Tests\Unit\Maintenance\Application;

use Maintenance\Application\GetMaintenanceDetail\GetMaintenanceDetailQuery;
use Maintenance\Application\GetMaintenanceDetail\GetMaintenanceDetailResponse;
use Maintenance\Application\GetMaintenanceDetail\MaintenanceNotFoundException;
use Maintenance\Domain\Maintenance;
use Maintenance\Domain\MaintenancePriority;
use Maintenance\Domain\MaintenanceReason;
use Maintenance\Domain\MaintenanceStatus;
use Maintenance\Domain\MaintenanceType;
use Mockery;
use Ramsey\Uuid\Uuid;

/**
 * Note: GetMaintenanceDetailHandler uses BikeEloquentModel directly (static method call)
 * which cannot be properly mocked in unit tests. The handler tests are covered in
 * integration/feature tests. Here we focus on testing the Query, Response, and Exception classes.
 */

describe('GetMaintenanceDetailQuery', function () {
    it('holds maintenance id', function () {
        $maintenanceId = Uuid::uuid4()->toString();
        $query = new GetMaintenanceDetailQuery(maintenanceId: $maintenanceId);

        expect($query->maintenanceId)->toBe($maintenanceId);
    });

    it('requires maintenance id', function () {
        $query = new GetMaintenanceDetailQuery(maintenanceId: 'maint-123');

        expect($query->maintenanceId)->toBe('maint-123');
    });
});

describe('GetMaintenanceDetailResponse', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('creates response from maintenance entity without bike', function () {
        $maintenanceId = Uuid::uuid4()->toString();
        $bikeId = Uuid::uuid4()->toString();
        $maintenance = createMaintenanceForDetail($maintenanceId, $bikeId, MaintenanceStatus::TODO);

        $response = GetMaintenanceDetailResponse::fromMaintenance($maintenance, null);

        expect($response)->toBeInstanceOf(GetMaintenanceDetailResponse::class);

        $array = $response->toArray();
        expect($array['id'])->toBe($maintenanceId);
        expect($array['bike_id'])->toBe($bikeId);
        expect($array['bike'])->toBeNull();
    });

    it('converts to array with all fields', function () {
        $maintenanceId = Uuid::uuid4()->toString();
        $bikeId = Uuid::uuid4()->toString();
        $maintenance = createMaintenanceForDetail($maintenanceId, $bikeId, MaintenanceStatus::COMPLETED);

        $response = GetMaintenanceDetailResponse::fromMaintenance($maintenance, null);
        $array = $response->toArray();

        expect($array)->toHaveKeys([
            'id',
            'bike_id',
            'type',
            'reason',
            'priority',
            'status',
            'description',
            'scheduled_at',
            'started_at',
            'completed_at',
            'work_description',
            'parts_replaced',
            'cost',
            'photos',
            'created_at',
            'updated_at',
            'bike',
        ]);
    });

    it('returns maintenance with todo status', function () {
        $maintenanceId = Uuid::uuid4()->toString();
        $bikeId = Uuid::uuid4()->toString();
        $maintenance = createMaintenanceForDetail($maintenanceId, $bikeId, MaintenanceStatus::TODO);

        $response = GetMaintenanceDetailResponse::fromMaintenance($maintenance, null);
        $array = $response->toArray();

        expect($array['status'])->toBe('todo');
        expect($array['started_at'])->toBeNull();
        expect($array['completed_at'])->toBeNull();
    });

    it('returns maintenance with in_progress status', function () {
        $maintenanceId = Uuid::uuid4()->toString();
        $bikeId = Uuid::uuid4()->toString();
        $maintenance = createMaintenanceForDetail($maintenanceId, $bikeId, MaintenanceStatus::IN_PROGRESS);

        $response = GetMaintenanceDetailResponse::fromMaintenance($maintenance, null);
        $array = $response->toArray();

        expect($array['status'])->toBe('in_progress');
        expect($array['started_at'])->not->toBeNull();
        expect($array['completed_at'])->toBeNull();
    });

    it('returns maintenance with completed status', function () {
        $maintenanceId = Uuid::uuid4()->toString();
        $bikeId = Uuid::uuid4()->toString();
        $maintenance = createMaintenanceForDetail($maintenanceId, $bikeId, MaintenanceStatus::COMPLETED);

        $response = GetMaintenanceDetailResponse::fromMaintenance($maintenance, null);
        $array = $response->toArray();

        expect($array['status'])->toBe('completed');
        expect($array['started_at'])->not->toBeNull();
        expect($array['completed_at'])->not->toBeNull();
        expect($array['work_description'])->toBe('Work completed');
    });

    it('returns maintenance with photos', function () {
        $maintenanceId = Uuid::uuid4()->toString();
        $bikeId = Uuid::uuid4()->toString();
        $photos = ['photo1.jpg', 'photo2.jpg', 'photo3.jpg'];
        $maintenance = createMaintenanceForDetail($maintenanceId, $bikeId, MaintenanceStatus::TODO, $photos);

        $response = GetMaintenanceDetailResponse::fromMaintenance($maintenance, null);
        $array = $response->toArray();

        expect($array['photos'])->toBe($photos);
        expect($array['photos'])->toHaveCount(3);
    });

    it('returns urgent maintenance with correct priority', function () {
        $maintenanceId = Uuid::uuid4()->toString();
        $bikeId = Uuid::uuid4()->toString();
        $maintenance = createMaintenanceForDetail(
            $maintenanceId,
            $bikeId,
            MaintenanceStatus::TODO,
            [],
            MaintenancePriority::URGENT
        );

        $response = GetMaintenanceDetailResponse::fromMaintenance($maintenance, null);
        $array = $response->toArray();

        expect($array['priority'])->toBe('urgent');
    });

    it('returns maintenance with curative type', function () {
        $maintenanceId = Uuid::uuid4()->toString();
        $bikeId = Uuid::uuid4()->toString();
        $maintenance = createMaintenanceForDetail(
            $maintenanceId,
            $bikeId,
            MaintenanceStatus::TODO,
            [],
            MaintenancePriority::NORMAL,
            MaintenanceType::CURATIVE
        );

        $response = GetMaintenanceDetailResponse::fromMaintenance($maintenance, null);
        $array = $response->toArray();

        expect($array['type'])->toBe('curative');
    });

    it('returns maintenance with cost in euros', function () {
        $maintenanceId = Uuid::uuid4()->toString();
        $bikeId = Uuid::uuid4()->toString();
        $maintenance = createMaintenanceForDetail($maintenanceId, $bikeId, MaintenanceStatus::COMPLETED);

        $response = GetMaintenanceDetailResponse::fromMaintenance($maintenance, null);
        $array = $response->toArray();

        // Cost is stored in cents (5000) and returned in euros (50.00)
        expect($array['cost'])->toBe(50.00);
    });

    // Note: The test 'includes bike information when provided' is skipped because
    // BikeEloquentModel is a final class and cannot be mocked.
    // This functionality is covered by integration tests.

    it('formats dates correctly', function () {
        $maintenanceId = Uuid::uuid4()->toString();
        $bikeId = Uuid::uuid4()->toString();
        $maintenance = createMaintenanceForDetail($maintenanceId, $bikeId, MaintenanceStatus::TODO);

        $response = GetMaintenanceDetailResponse::fromMaintenance($maintenance, null);
        $array = $response->toArray();

        // Check date format is Y-m-d H:i:s
        expect($array['scheduled_at'])->toMatch('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/');
        expect($array['created_at'])->toMatch('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/');
        expect($array['updated_at'])->toMatch('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/');
    });

    it('returns null for parts replaced when not completed', function () {
        $maintenanceId = Uuid::uuid4()->toString();
        $bikeId = Uuid::uuid4()->toString();
        $maintenance = createMaintenanceForDetail($maintenanceId, $bikeId, MaintenanceStatus::TODO);

        $response = GetMaintenanceDetailResponse::fromMaintenance($maintenance, null);
        $array = $response->toArray();

        expect($array['parts_replaced'])->toBeNull();
        expect($array['work_description'])->toBeNull();
    });

    it('returns parts replaced when completed', function () {
        $maintenanceId = Uuid::uuid4()->toString();
        $bikeId = Uuid::uuid4()->toString();
        $maintenance = createMaintenanceForDetail($maintenanceId, $bikeId, MaintenanceStatus::COMPLETED);

        $response = GetMaintenanceDetailResponse::fromMaintenance($maintenance, null);
        $array = $response->toArray();

        expect($array['parts_replaced'])->toBe('Chain, Brake pads');
        expect($array['work_description'])->toBe('Work completed');
    });
});

describe('MaintenanceNotFoundException', function () {
    it('creates exception with maintenance id in message', function () {
        $maintenanceId = Uuid::uuid4()->toString();
        $exception = new MaintenanceNotFoundException($maintenanceId);

        expect($exception->getMessage())->toContain($maintenanceId);
        expect($exception->getMessage())->toContain('not found');
    });

    it('extends Exception class', function () {
        $exception = new MaintenanceNotFoundException('test-id');

        expect($exception)->toBeInstanceOf(\Exception::class);
    });
});

function createMaintenanceForDetail(
    string $id,
    string $bikeId,
    MaintenanceStatus $status = MaintenanceStatus::TODO,
    array $photos = [],
    MaintenancePriority $priority = MaintenancePriority::NORMAL,
    MaintenanceType $type = MaintenanceType::PREVENTIVE,
): Maintenance {
    $now = new \DateTimeImmutable();

    return Maintenance::reconstitute(
        id: $id,
        bikeId: $bikeId,
        type: $type,
        reason: MaintenanceReason::FULL_SERVICE_BASIC,
        priority: $priority,
        status: $status,
        description: 'Test maintenance description',
        scheduledAt: $now,
        startedAt: $status === MaintenanceStatus::IN_PROGRESS || $status === MaintenanceStatus::COMPLETED
            ? $now
            : null,
        completedAt: $status === MaintenanceStatus::COMPLETED
            ? $now
            : null,
        workDescription: $status === MaintenanceStatus::COMPLETED ? 'Work completed' : null,
        partsReplaced: $status === MaintenanceStatus::COMPLETED ? 'Chain, Brake pads' : null,
        cost: $status === MaintenanceStatus::COMPLETED ? 5000 : null,
        photos: $photos,
        createdAt: $now,
        updatedAt: $now,
    );
}
