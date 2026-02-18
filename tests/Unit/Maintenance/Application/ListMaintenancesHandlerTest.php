<?php

declare(strict_types=1);

namespace Tests\Unit\Maintenance\Application;

use Illuminate\Support\Collection;
use Maintenance\Application\ListMaintenances\ListMaintenancesQuery;
use Maintenance\Application\ListMaintenances\ListMaintenancesResponse;
use Maintenance\Domain\Maintenance;
use Maintenance\Domain\MaintenancePriority;
use Maintenance\Domain\MaintenanceReason;
use Maintenance\Domain\MaintenanceStatus;
use Maintenance\Domain\MaintenanceType;
use Mockery;
use Ramsey\Uuid\Uuid;

/**
 * Note: ListMaintenancesHandler uses BikeEloquentModel directly (static method call)
 * which cannot be properly mocked in unit tests. The handler tests are covered in
 * integration/feature tests. Here we focus on testing the Query and Response classes.
 */

describe('ListMaintenancesQuery', function () {
    it('holds all filter parameters', function () {
        $query = new ListMaintenancesQuery(
            bikeId: 'bike-123',
            status: 'todo',
            priority: 'urgent',
            dateFrom: '2024-01-01',
            dateTo: '2024-12-31',
        );

        expect($query->bikeId)->toBe('bike-123');
        expect($query->status)->toBe('todo');
        expect($query->priority)->toBe('urgent');
        expect($query->dateFrom)->toBe('2024-01-01');
        expect($query->dateTo)->toBe('2024-12-31');
    });

    it('has default null values for all filters', function () {
        $query = new ListMaintenancesQuery();

        expect($query->bikeId)->toBeNull();
        expect($query->status)->toBeNull();
        expect($query->priority)->toBeNull();
        expect($query->dateFrom)->toBeNull();
        expect($query->dateTo)->toBeNull();
    });

    it('allows partial filter parameters', function () {
        $query = new ListMaintenancesQuery(
            status: 'in_progress',
        );

        expect($query->bikeId)->toBeNull();
        expect($query->status)->toBe('in_progress');
        expect($query->priority)->toBeNull();
    });

    it('accepts only bikeId filter', function () {
        $bikeId = Uuid::uuid4()->toString();
        $query = new ListMaintenancesQuery(bikeId: $bikeId);

        expect($query->bikeId)->toBe($bikeId);
        expect($query->status)->toBeNull();
    });

    it('accepts date range filters', function () {
        $query = new ListMaintenancesQuery(
            dateFrom: '2024-01-01',
            dateTo: '2024-01-31',
        );

        expect($query->dateFrom)->toBe('2024-01-01');
        expect($query->dateTo)->toBe('2024-01-31');
    });
});

describe('ListMaintenancesResponse', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('converts to array with empty maintenances', function () {
        $bikes = new Collection();
        $countsByStatus = ['todo' => 0, 'in_progress' => 0, 'completed' => 0];

        $response = new ListMaintenancesResponse(
            maintenances: [],
            bikes: $bikes,
            countsByStatus: $countsByStatus,
            total: 0,
        );

        $array = $response->toArray();

        expect($array)->toHaveKeys(['maintenances', 'counts_by_status', 'total']);
        expect($array['total'])->toBe(0);
        expect($array['maintenances'])->toBeEmpty();
        expect($array['counts_by_status'])->toBe($countsByStatus);
    });

    it('converts to array with maintenances', function () {
        $maintenances = [
            createMaintenanceForList('maint-1', 'bike-1', MaintenanceStatus::TODO),
        ];
        $bikes = new Collection();
        $countsByStatus = ['todo' => 1, 'in_progress' => 0, 'completed' => 0];

        $response = new ListMaintenancesResponse(
            maintenances: $maintenances,
            bikes: $bikes,
            countsByStatus: $countsByStatus,
            total: 1,
        );

        $array = $response->toArray();

        expect($array['total'])->toBe(1);
        expect($array['maintenances'])->toHaveCount(1);
        expect($array['maintenances'][0]['id'])->toBe('maint-1');
        expect($array['maintenances'][0]['bike_id'])->toBe('bike-1');
    });

    it('includes maintenance details in array', function () {
        $maintenances = [
            createMaintenanceForList('maint-1', 'bike-1', MaintenanceStatus::TODO, MaintenancePriority::URGENT),
        ];
        $bikes = new Collection();
        $countsByStatus = ['todo' => 1];

        $response = new ListMaintenancesResponse(
            maintenances: $maintenances,
            bikes: $bikes,
            countsByStatus: $countsByStatus,
            total: 1,
        );

        $array = $response->toArray();
        $maintenance = $array['maintenances'][0];

        expect($maintenance)->toHaveKeys([
            'id',
            'bike_id',
            'bike',
            'type',
            'type_label',
            'reason',
            'reason_label',
            'category',
            'category_label',
            'priority',
            'is_urgent',
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
        ]);
    });

    it('returns correct priority and urgency flags', function () {
        $maintenances = [
            createMaintenanceForList('maint-1', 'bike-1', MaintenanceStatus::TODO, MaintenancePriority::URGENT),
            createMaintenanceForList('maint-2', 'bike-2', MaintenanceStatus::TODO, MaintenancePriority::NORMAL),
        ];
        $bikes = new Collection();

        $response = new ListMaintenancesResponse(
            maintenances: $maintenances,
            bikes: $bikes,
            countsByStatus: ['todo' => 2],
            total: 2,
        );

        $array = $response->toArray();

        expect($array['maintenances'][0]['priority'])->toBe('urgent');
        expect($array['maintenances'][0]['is_urgent'])->toBeTrue();
        expect($array['maintenances'][1]['priority'])->toBe('normal');
        expect($array['maintenances'][1]['is_urgent'])->toBeFalse();
    });

    it('returns correct status for each maintenance', function () {
        $maintenances = [
            createMaintenanceForList('maint-1', 'bike-1', MaintenanceStatus::TODO),
            createMaintenanceForList('maint-2', 'bike-2', MaintenanceStatus::IN_PROGRESS),
            createMaintenanceForList('maint-3', 'bike-3', MaintenanceStatus::COMPLETED),
        ];
        $bikes = new Collection();

        $response = new ListMaintenancesResponse(
            maintenances: $maintenances,
            bikes: $bikes,
            countsByStatus: ['todo' => 1, 'in_progress' => 1, 'completed' => 1],
            total: 3,
        );

        $array = $response->toArray();

        expect($array['maintenances'][0]['status'])->toBe('todo');
        expect($array['maintenances'][1]['status'])->toBe('in_progress');
        expect($array['maintenances'][2]['status'])->toBe('completed');
    });

    it('includes bike information when available', function () {
        $bikeId = Uuid::uuid4()->toString();
        $maintenances = [
            createMaintenanceForList('maint-1', $bikeId, MaintenanceStatus::TODO),
        ];

        $bikeMock = Mockery::mock();
        $bikeMock->id = $bikeId;
        $bikeMock->internal_number = 'BIKE-001';
        $bikeMock->category_id = 'cat-1';

        $modelMock = Mockery::mock();
        $modelMock->name = 'Trail 29';

        $brandMock = Mockery::mock();
        $brandMock->name = 'Trek';
        $modelMock->brand = $brandMock;

        $bikeMock->model = $modelMock;

        $categoryMock = Mockery::mock();
        $categoryMock->name = 'VTT';
        $bikeMock->category = $categoryMock;

        $bikes = new Collection([$bikeId => $bikeMock]);

        $response = new ListMaintenancesResponse(
            maintenances: $maintenances,
            bikes: $bikes,
            countsByStatus: ['todo' => 1],
            total: 1,
        );

        $array = $response->toArray();

        expect($array['maintenances'][0]['bike'])->not->toBeNull();
        expect($array['maintenances'][0]['bike']['internal_number'])->toBe('BIKE-001');
        expect($array['maintenances'][0]['bike']['brand'])->toBe('Trek');
        expect($array['maintenances'][0]['bike']['model'])->toBe('Trail 29');
        expect($array['maintenances'][0]['bike']['category_name'])->toBe('VTT');
    });

    it('returns null for bike when not found in collection', function () {
        $maintenances = [
            createMaintenanceForList('maint-1', 'bike-not-in-collection', MaintenanceStatus::TODO),
        ];
        $bikes = new Collection();

        $response = new ListMaintenancesResponse(
            maintenances: $maintenances,
            bikes: $bikes,
            countsByStatus: ['todo' => 1],
            total: 1,
        );

        $array = $response->toArray();

        expect($array['maintenances'][0]['bike'])->toBeNull();
    });

    it('returns correct counts by status', function () {
        $countsByStatus = [
            'todo' => 5,
            'in_progress' => 3,
            'completed' => 10,
        ];

        $response = new ListMaintenancesResponse(
            maintenances: [],
            bikes: new Collection(),
            countsByStatus: $countsByStatus,
            total: 0,
        );

        $array = $response->toArray();

        expect($array['counts_by_status'])->toBe($countsByStatus);
        expect($array['counts_by_status']['todo'])->toBe(5);
        expect($array['counts_by_status']['in_progress'])->toBe(3);
        expect($array['counts_by_status']['completed'])->toBe(10);
    });

    it('includes type and reason labels', function () {
        $maintenances = [
            createMaintenanceForList('maint-1', 'bike-1', MaintenanceStatus::TODO),
        ];

        $response = new ListMaintenancesResponse(
            maintenances: $maintenances,
            bikes: new Collection(),
            countsByStatus: ['todo' => 1],
            total: 1,
        );

        $array = $response->toArray();
        $maintenance = $array['maintenances'][0];

        expect($maintenance['type'])->toBe('preventive');
        expect($maintenance['type_label'])->not->toBeEmpty();
        expect($maintenance['reason'])->toBe('full_service_basic');
        expect($maintenance['reason_label'])->not->toBeEmpty();
        expect($maintenance['category'])->not->toBeEmpty();
        expect($maintenance['category_label'])->not->toBeEmpty();
    });

    it('formats dates correctly', function () {
        $maintenances = [
            createMaintenanceForList('maint-1', 'bike-1', MaintenanceStatus::COMPLETED),
        ];

        $response = new ListMaintenancesResponse(
            maintenances: $maintenances,
            bikes: new Collection(),
            countsByStatus: ['completed' => 1],
            total: 1,
        );

        $array = $response->toArray();
        $maintenance = $array['maintenances'][0];

        // Check date format is Y-m-d H:i:s
        expect($maintenance['scheduled_at'])->toMatch('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/');
        expect($maintenance['created_at'])->toMatch('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/');
        expect($maintenance['updated_at'])->toMatch('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/');
    });

    it('includes empty photos array', function () {
        $maintenances = [
            createMaintenanceForList('maint-1', 'bike-1', MaintenanceStatus::TODO),
        ];

        $response = new ListMaintenancesResponse(
            maintenances: $maintenances,
            bikes: new Collection(),
            countsByStatus: ['todo' => 1],
            total: 1,
        );

        $array = $response->toArray();

        expect($array['maintenances'][0]['photos'])->toBeArray();
        expect($array['maintenances'][0]['photos'])->toBeEmpty();
    });
});

function createMaintenanceForList(
    string $id,
    string $bikeId,
    MaintenanceStatus $status,
    MaintenancePriority $priority = MaintenancePriority::NORMAL
): Maintenance {
    return Maintenance::reconstitute(
        id: $id,
        bikeId: $bikeId,
        type: MaintenanceType::PREVENTIVE,
        reason: MaintenanceReason::FULL_SERVICE_BASIC,
        priority: $priority,
        status: $status,
        description: 'Test maintenance',
        scheduledAt: new \DateTimeImmutable(),
        startedAt: $status === MaintenanceStatus::IN_PROGRESS || $status === MaintenanceStatus::COMPLETED
            ? new \DateTimeImmutable()
            : null,
        completedAt: $status === MaintenanceStatus::COMPLETED
            ? new \DateTimeImmutable()
            : null,
        workDescription: $status === MaintenanceStatus::COMPLETED ? 'Work done' : null,
        partsReplaced: null,
        cost: $status === MaintenanceStatus::COMPLETED ? 5000 : null,
        photos: [],
        createdAt: new \DateTimeImmutable(),
        updatedAt: new \DateTimeImmutable(),
    );
}
