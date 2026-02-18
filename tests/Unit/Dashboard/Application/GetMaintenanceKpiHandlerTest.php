<?php

declare(strict_types=1);

namespace Tests\Unit\Dashboard\Application;

use Dashboard\Application\GetMaintenanceKpi\GetMaintenanceKpiHandler;
use Dashboard\Application\GetMaintenanceKpi\GetMaintenanceKpiQuery;
use Dashboard\Application\GetMaintenanceKpi\GetMaintenanceKpiResponse;
use Maintenance\Domain\MaintenanceRepositoryInterface;

describe('GetMaintenanceKpiHandler', function () {
    it('returns maintenance KPIs with default 30-day period when no dates provided', function () {
        $maintenanceRepository = \Mockery::mock(MaintenanceRepositoryInterface::class);

        $maintenanceRepository->shouldReceive('countByStatus')
            ->once()
            ->andReturn([
                'completed' => 15,
                'in_progress' => 3,
                'todo' => 5,
            ]);

        $handler = new GetMaintenanceKpiHandler($maintenanceRepository);
        $query = new GetMaintenanceKpiQuery();

        $response = $handler->handle($query);

        expect($response)->toBeInstanceOf(GetMaintenanceKpiResponse::class);
        expect($response->totalCompleted)->toBe(15);
        expect($response->totalInProgress)->toBe(3);
        expect($response->totalTodo)->toBe(5);
        expect($response->total)->toBe(23);
        expect($response->byStatus)->toBe([
            'completed' => 15,
            'in_progress' => 3,
            'todo' => 5,
        ]);
        expect($response->period)->toHaveKeys(['from', 'to', 'days']);
        expect($response->period['days'])->toBe(31); // 30 days + 1
    });

    it('returns maintenance KPIs with custom date range', function () {
        $maintenanceRepository = \Mockery::mock(MaintenanceRepositoryInterface::class);

        $maintenanceRepository->shouldReceive('countByStatus')
            ->once()
            ->andReturn([
                'completed' => 10,
                'in_progress' => 2,
                'todo' => 3,
            ]);

        $handler = new GetMaintenanceKpiHandler($maintenanceRepository);

        $dateFrom = new \DateTimeImmutable('2024-01-01');
        $dateTo = new \DateTimeImmutable('2024-01-15');
        $query = new GetMaintenanceKpiQuery($dateFrom, $dateTo);

        $response = $handler->handle($query);

        expect($response)->toBeInstanceOf(GetMaintenanceKpiResponse::class);
        expect($response->period['from'])->toBe('2024-01-01');
        expect($response->period['to'])->toBe('2024-01-15');
        expect($response->period['days'])->toBe(15); // 14 days + 1
        expect($response->totalCompleted)->toBe(10);
        expect($response->totalInProgress)->toBe(2);
        expect($response->totalTodo)->toBe(3);
        expect($response->total)->toBe(15);
    });

    it('handles empty maintenance data', function () {
        $maintenanceRepository = \Mockery::mock(MaintenanceRepositoryInterface::class);

        $maintenanceRepository->shouldReceive('countByStatus')
            ->once()
            ->andReturn([]);

        $handler = new GetMaintenanceKpiHandler($maintenanceRepository);
        $query = new GetMaintenanceKpiQuery();

        $response = $handler->handle($query);

        expect($response->totalCompleted)->toBe(0);
        expect($response->totalInProgress)->toBe(0);
        expect($response->totalTodo)->toBe(0);
        expect($response->total)->toBe(0);
        expect($response->byStatus)->toBe([]);
    });

    it('handles partial maintenance status data', function () {
        $maintenanceRepository = \Mockery::mock(MaintenanceRepositoryInterface::class);

        $maintenanceRepository->shouldReceive('countByStatus')
            ->once()
            ->andReturn([
                'completed' => 5,
                // in_progress and todo are missing
            ]);

        $handler = new GetMaintenanceKpiHandler($maintenanceRepository);
        $query = new GetMaintenanceKpiQuery();

        $response = $handler->handle($query);

        expect($response->totalCompleted)->toBe(5);
        expect($response->totalInProgress)->toBe(0);
        expect($response->totalTodo)->toBe(0);
        expect($response->total)->toBe(5);
    });
});

describe('GetMaintenanceKpiResponse', function () {
    it('can be converted to array', function () {
        $response = new GetMaintenanceKpiResponse(
            period: [
                'from' => '2024-01-01',
                'to' => '2024-01-31',
                'days' => 31,
            ],
            totalCompleted: 20,
            totalInProgress: 5,
            totalTodo: 8,
            total: 33,
            byStatus: [
                'completed' => 20,
                'in_progress' => 5,
                'todo' => 8,
            ],
        );

        $array = $response->toArray();

        expect($array)->toHaveKeys([
            'period',
            'total_completed',
            'total_in_progress',
            'total_todo',
            'total',
            'by_status',
        ]);
        expect($array['total_completed'])->toBe(20);
        expect($array['total_in_progress'])->toBe(5);
        expect($array['total_todo'])->toBe(8);
        expect($array['total'])->toBe(33);
        expect($array['period']['from'])->toBe('2024-01-01');
    });
});
