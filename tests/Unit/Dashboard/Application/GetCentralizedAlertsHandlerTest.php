<?php

declare(strict_types=1);

namespace Tests\Unit\Dashboard\Application;

use Dashboard\Application\GetCentralizedAlerts\GetCentralizedAlertsHandler;
use Dashboard\Application\GetCentralizedAlerts\GetCentralizedAlertsResponse;
use Fleet\Domain\BikeRepositoryInterface;
use Maintenance\Domain\Maintenance;
use Maintenance\Domain\MaintenancePriority;
use Maintenance\Domain\MaintenanceReason;
use Maintenance\Domain\MaintenanceRepositoryInterface;
use Maintenance\Domain\MaintenanceStatus;
use Maintenance\Domain\MaintenanceType;
use Rental\Domain\DepositStatus;
use Rental\Domain\Rental;
use Rental\Domain\RentalDuration;
use Rental\Domain\RentalItem;
use Rental\Domain\RentalRepositoryInterface;
use Rental\Domain\RentalStatus;

/**
 * Helper function to create a test Rental instance
 */
function createTestRental(
    string $id = 'rental-001',
    string $customerId = 'customer-001',
    ?\DateTimeImmutable $expectedReturnDate = null,
    ?array $items = null,
): Rental {
    $now = new \DateTimeImmutable();
    $expectedReturnDate = $expectedReturnDate ?? $now->modify('+1 day');

    // Create a default rental item if none provided
    if ($items === null) {
        $items = [
            new RentalItem(
                id: 'item-001',
                rentalId: $id,
                bikeId: 'bike-123',
                dailyRate: 50.0,
                quantity: 1,
            ),
        ];
    }

    return new Rental(
        id: $id,
        customerId: $customerId,
        startDate: $now,
        expectedReturnDate: $expectedReturnDate,
        actualReturnDate: null,
        duration: RentalDuration::FULL_DAY,
        depositAmount: 100.0,
        totalAmount: 50.0,
        discountAmount: 0.0,
        taxRate: 20.0,
        taxAmount: 10.0,
        totalWithTax: 60.0,
        status: RentalStatus::ACTIVE,
        items: $items,
        equipments: [],
        depositStatus: DepositStatus::HELD,
        depositRetained: null,
        cancellationReason: null,
        createdAt: $now,
        updatedAt: $now,
    );
}

describe('GetCentralizedAlertsHandler', function () {
    it('returns empty alerts when no issues exist', function () {
        $rentalRepository = \Mockery::mock(RentalRepositoryInterface::class);
        $maintenanceRepository = \Mockery::mock(MaintenanceRepositoryInterface::class);
        $bikeRepository = \Mockery::mock(BikeRepositoryInterface::class);

        $rentalRepository->shouldReceive('findLateRentals')
            ->once()
            ->andReturn([]);

        $maintenanceRepository->shouldReceive('findWithFilters')
            ->once()
            ->withArgs(function ($bikeId, $status, $priority) {
                return $bikeId === null
                    && $status === MaintenanceStatus::TODO
                    && $priority === MaintenancePriority::URGENT;
            })
            ->andReturn([]);

        $bikeRepository->shouldReceive('findLongUnavailable')
            ->once()
            ->with(5)
            ->andReturn([]);

        $maintenanceRepository->shouldReceive('findLongRunning')
            ->once()
            ->with(7)
            ->andReturn([]);

        $handler = new GetCentralizedAlertsHandler(
            $rentalRepository,
            $maintenanceRepository,
            $bikeRepository,
        );

        $response = $handler->handle();

        expect($response)->toBeInstanceOf(GetCentralizedAlertsResponse::class);
        expect($response->alerts)->toBe([]);
        expect($response->total)->toBe(0);
        expect($response->countsBySeverity)->toBe([
            'high' => 0,
            'medium' => 0,
            'low' => 0,
        ]);
    });

    it('returns late rental alerts with high severity', function () {
        $rentalRepository = \Mockery::mock(RentalRepositoryInterface::class);
        $maintenanceRepository = \Mockery::mock(MaintenanceRepositoryInterface::class);
        $bikeRepository = \Mockery::mock(BikeRepositoryInterface::class);

        // Create a late rental (expected return 3 days ago)
        $lateRental = createTestRental(
            id: 'rental-001',
            customerId: 'customer-001',
            expectedReturnDate: new \DateTimeImmutable('-3 days'),
        );

        $rentalRepository->shouldReceive('findLateRentals')
            ->once()
            ->andReturn([$lateRental]);

        $maintenanceRepository->shouldReceive('findWithFilters')
            ->once()
            ->andReturn([]);

        $bikeRepository->shouldReceive('findLongUnavailable')
            ->once()
            ->andReturn([]);

        $maintenanceRepository->shouldReceive('findLongRunning')
            ->once()
            ->andReturn([]);

        $handler = new GetCentralizedAlertsHandler(
            $rentalRepository,
            $maintenanceRepository,
            $bikeRepository,
        );

        $response = $handler->handle();

        expect($response->total)->toBe(1);
        expect($response->countsBySeverity['high'])->toBe(1);
        expect($response->alerts[0]['type'])->toBe('late_return');
        expect($response->alerts[0]['severity'])->toBe('high');
        expect($response->alerts[0]['rental_id'])->toBe('rental-001');
        expect($response->alerts[0]['customer_id'])->toBe('customer-001');
        expect($response->alerts[0]['bike_id'])->toBe('bike-123');
        expect($response->alerts[0]['days_late'])->toBeGreaterThanOrEqual(3);
    });

    it('returns urgent maintenance alerts with high severity', function () {
        $rentalRepository = \Mockery::mock(RentalRepositoryInterface::class);
        $maintenanceRepository = \Mockery::mock(MaintenanceRepositoryInterface::class);
        $bikeRepository = \Mockery::mock(BikeRepositoryInterface::class);

        $rentalRepository->shouldReceive('findLateRentals')
            ->once()
            ->andReturn([]);

        // Create a mock urgent maintenance
        $urgentMaintenance = Maintenance::declare(
            id: 'maint-001',
            bikeId: 'bike-456',
            type: MaintenanceType::CURATIVE,
            reason: MaintenanceReason::BRAKE_PAD_REPLACEMENT,
            priority: MaintenancePriority::URGENT,
            description: 'Urgent brake repair needed',
        );

        $maintenanceRepository->shouldReceive('findWithFilters')
            ->once()
            ->andReturn([$urgentMaintenance]);

        $bikeRepository->shouldReceive('findLongUnavailable')
            ->once()
            ->andReturn([]);

        $maintenanceRepository->shouldReceive('findLongRunning')
            ->once()
            ->andReturn([]);

        $handler = new GetCentralizedAlertsHandler(
            $rentalRepository,
            $maintenanceRepository,
            $bikeRepository,
        );

        $response = $handler->handle();

        expect($response->total)->toBe(1);
        expect($response->countsBySeverity['high'])->toBe(1);
        expect($response->alerts[0]['type'])->toBe('urgent_maintenance');
        expect($response->alerts[0]['severity'])->toBe('high');
        expect($response->alerts[0]['maintenance_id'])->toBe('maint-001');
        expect($response->alerts[0]['bike_id'])->toBe('bike-456');
    });

    it('returns long unavailable bike alerts with medium severity', function () {
        $rentalRepository = \Mockery::mock(RentalRepositoryInterface::class);
        $maintenanceRepository = \Mockery::mock(MaintenanceRepositoryInterface::class);
        $bikeRepository = \Mockery::mock(BikeRepositoryInterface::class);

        $rentalRepository->shouldReceive('findLateRentals')
            ->once()
            ->andReturn([]);

        $maintenanceRepository->shouldReceive('findWithFilters')
            ->once()
            ->andReturn([]);

        $bikeRepository->shouldReceive('findLongUnavailable')
            ->once()
            ->with(5)
            ->andReturn([
                [
                    'bike_id' => 'bike-789',
                    'internal_number' => 'VTT-001',
                    'days_unavailable' => 10,
                ],
            ]);

        $maintenanceRepository->shouldReceive('findLongRunning')
            ->once()
            ->andReturn([]);

        $handler = new GetCentralizedAlertsHandler(
            $rentalRepository,
            $maintenanceRepository,
            $bikeRepository,
        );

        $response = $handler->handle();

        expect($response->total)->toBe(1);
        expect($response->countsBySeverity['medium'])->toBe(1);
        expect($response->alerts[0]['type'])->toBe('bike_long_unavailable');
        expect($response->alerts[0]['severity'])->toBe('medium');
        expect($response->alerts[0]['bike_id'])->toBe('bike-789');
        expect($response->alerts[0]['internal_number'])->toBe('VTT-001');
        expect($response->alerts[0]['days_unavailable'])->toBe(10);
    });

    it('returns long running maintenance alerts with appropriate severity', function () {
        $rentalRepository = \Mockery::mock(RentalRepositoryInterface::class);
        $maintenanceRepository = \Mockery::mock(MaintenanceRepositoryInterface::class);
        $bikeRepository = \Mockery::mock(BikeRepositoryInterface::class);

        $rentalRepository->shouldReceive('findLateRentals')
            ->once()
            ->andReturn([]);

        $maintenanceRepository->shouldReceive('findWithFilters')
            ->once()
            ->andReturn([]);

        $bikeRepository->shouldReceive('findLongUnavailable')
            ->once()
            ->andReturn([]);

        $maintenanceRepository->shouldReceive('findLongRunning')
            ->once()
            ->with(7)
            ->andReturn([
                [
                    'maintenance_id' => 'maint-002',
                    'bike_id' => 'bike-111',
                    'days_in_progress' => 14,
                    'priority' => 'urgent',
                ],
                [
                    'maintenance_id' => 'maint-003',
                    'bike_id' => 'bike-222',
                    'days_in_progress' => 10,
                    'priority' => 'normal',
                ],
            ]);

        $handler = new GetCentralizedAlertsHandler(
            $rentalRepository,
            $maintenanceRepository,
            $bikeRepository,
        );

        $response = $handler->handle();

        expect($response->total)->toBe(2);
        expect($response->countsBySeverity['high'])->toBe(1);
        expect($response->countsBySeverity['medium'])->toBe(1);

        // Urgent priority should have high severity
        $urgentAlert = collect($response->alerts)->firstWhere('maintenance_id', 'maint-002');
        expect($urgentAlert['severity'])->toBe('high');
        expect($urgentAlert['days_in_progress'])->toBe(14);

        // Normal priority should have medium severity
        $normalAlert = collect($response->alerts)->firstWhere('maintenance_id', 'maint-003');
        expect($normalAlert['severity'])->toBe('medium');
        expect($normalAlert['days_in_progress'])->toBe(10);
    });

    it('sorts alerts by severity then by date', function () {
        $rentalRepository = \Mockery::mock(RentalRepositoryInterface::class);
        $maintenanceRepository = \Mockery::mock(MaintenanceRepositoryInterface::class);
        $bikeRepository = \Mockery::mock(BikeRepositoryInterface::class);

        // Create a late rental (high severity)
        $lateRental = createTestRental(
            id: 'rental-001',
            customerId: 'customer-001',
            expectedReturnDate: new \DateTimeImmutable('-2 days'),
        );

        $rentalRepository->shouldReceive('findLateRentals')
            ->once()
            ->andReturn([$lateRental]);

        $maintenanceRepository->shouldReceive('findWithFilters')
            ->once()
            ->andReturn([]);

        // Medium severity alert
        $bikeRepository->shouldReceive('findLongUnavailable')
            ->once()
            ->andReturn([
                [
                    'bike_id' => 'bike-789',
                    'internal_number' => 'VTT-001',
                    'days_unavailable' => 10,
                ],
            ]);

        $maintenanceRepository->shouldReceive('findLongRunning')
            ->once()
            ->andReturn([]);

        $handler = new GetCentralizedAlertsHandler(
            $rentalRepository,
            $maintenanceRepository,
            $bikeRepository,
        );

        $response = $handler->handle();

        expect($response->total)->toBe(2);
        // High severity should come first
        expect($response->alerts[0]['severity'])->toBe('high');
        expect($response->alerts[1]['severity'])->toBe('medium');
    });

    it('combines all alert types correctly', function () {
        $rentalRepository = \Mockery::mock(RentalRepositoryInterface::class);
        $maintenanceRepository = \Mockery::mock(MaintenanceRepositoryInterface::class);
        $bikeRepository = \Mockery::mock(BikeRepositoryInterface::class);

        // Late rental
        $lateRental = createTestRental(
            id: 'rental-001',
            customerId: 'customer-001',
            expectedReturnDate: new \DateTimeImmutable('-1 day'),
        );

        $rentalRepository->shouldReceive('findLateRentals')
            ->once()
            ->andReturn([$lateRental]);

        // Urgent maintenance
        $urgentMaintenance = Maintenance::declare(
            id: 'maint-001',
            bikeId: 'bike-456',
            type: MaintenanceType::CURATIVE,
            reason: MaintenanceReason::TIRE_REPLACEMENT,
            priority: MaintenancePriority::URGENT,
        );

        $maintenanceRepository->shouldReceive('findWithFilters')
            ->once()
            ->andReturn([$urgentMaintenance]);

        // Long unavailable bikes
        $bikeRepository->shouldReceive('findLongUnavailable')
            ->once()
            ->andReturn([
                [
                    'bike_id' => 'bike-789',
                    'internal_number' => 'VTT-001',
                    'days_unavailable' => 8,
                ],
            ]);

        // Long running maintenances
        $maintenanceRepository->shouldReceive('findLongRunning')
            ->once()
            ->andReturn([
                [
                    'maintenance_id' => 'maint-002',
                    'bike_id' => 'bike-111',
                    'days_in_progress' => 10,
                    'priority' => 'normal',
                ],
            ]);

        $handler = new GetCentralizedAlertsHandler(
            $rentalRepository,
            $maintenanceRepository,
            $bikeRepository,
        );

        $response = $handler->handle();

        expect($response->total)->toBe(4);
        expect($response->countsBySeverity['high'])->toBe(2); // late rental + urgent maintenance
        expect($response->countsBySeverity['medium'])->toBe(2); // unavailable bike + long running maintenance
        expect($response->countsBySeverity['low'])->toBe(0);

        // Verify all alert types are present
        $types = array_column($response->alerts, 'type');
        expect($types)->toContain('late_return');
        expect($types)->toContain('urgent_maintenance');
        expect($types)->toContain('bike_long_unavailable');
        expect($types)->toContain('maintenance_long_running');
    });
});

describe('GetCentralizedAlertsResponse', function () {
    it('can be converted to array', function () {
        $response = new GetCentralizedAlertsResponse(
            alerts: [
                [
                    'type' => 'late_return',
                    'severity' => 'high',
                    'rental_id' => 'rental-001',
                    'message' => 'Retour en retard de 2 jour(s)',
                ],
            ],
            countsBySeverity: [
                'high' => 1,
                'medium' => 0,
                'low' => 0,
            ],
            total: 1,
        );

        $array = $response->toArray();

        expect($array)->toHaveKeys(['alerts', 'counts_by_severity', 'total']);
        expect($array['total'])->toBe(1);
        expect($array['counts_by_severity']['high'])->toBe(1);
        expect($array['alerts'][0]['type'])->toBe('late_return');
    });
});
