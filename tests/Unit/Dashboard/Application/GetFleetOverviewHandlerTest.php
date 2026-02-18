<?php

declare(strict_types=1);

namespace Tests\Unit\Dashboard\Application;

use Customer\Domain\CustomerRepositoryInterface;
use Dashboard\Application\GetFleetOverview\GetFleetOverviewHandler;
use Dashboard\Application\GetFleetOverview\GetFleetOverviewResponse;
use Fleet\Domain\BikeRepositoryInterface;
use Maintenance\Domain\MaintenanceRepositoryInterface;
use Rental\Domain\RentalRepositoryInterface;

describe('GetFleetOverviewHandler', function () {
    it('returns fleet overview with all summaries', function () {
        $bikeRepository = \Mockery::mock(BikeRepositoryInterface::class);
        $rentalRepository = \Mockery::mock(RentalRepositoryInterface::class);
        $maintenanceRepository = \Mockery::mock(MaintenanceRepositoryInterface::class);
        $customerRepository = \Mockery::mock(CustomerRepositoryInterface::class);

        $bikeRepository->shouldReceive('countByStatus')
            ->once()
            ->andReturn(['available' => 10, 'rented' => 5, 'maintenance' => 2, 'retired' => 1]);

        $bikeRepository->shouldReceive('countActive')
            ->once()
            ->andReturn(15);

        $bikeRepository->shouldReceive('getAverageAge')
            ->once()
            ->andReturn(2.5);

        $rentalRepository->shouldReceive('countActive')
            ->once()
            ->andReturn(5);

        $maintenanceRepository->shouldReceive('countByStatus')
            ->once()
            ->andReturn(['todo' => 3, 'in_progress' => 1, 'completed' => 10]);

        $customerRepository->shouldReceive('count')
            ->once()
            ->andReturn(50);

        $handler = new GetFleetOverviewHandler(
            $bikeRepository,
            $rentalRepository,
            $maintenanceRepository,
            $customerRepository,
        );

        $response = $handler->handle();

        expect($response)->toBeInstanceOf(GetFleetOverviewResponse::class);
        expect($response->fleetSummary['total_bikes'])->toBe(18);
        expect($response->fleetSummary['active_bikes'])->toBe(15);
        expect($response->fleetSummary['average_age_years'])->toBe(2.5);
        expect($response->fleetSummary['by_status'])->toHaveKey('available');
        expect($response->rentalsSummary['active_rentals'])->toBe(5);
        expect($response->maintenanceSummary['by_status']['todo'])->toBe(3);
        expect($response->maintenanceSummary['urgent_pending'])->toBe(3);
        expect($response->customersSummary['total_customers'])->toBe(50);
    });

    it('handles empty bike fleet', function () {
        $bikeRepository = \Mockery::mock(BikeRepositoryInterface::class);
        $rentalRepository = \Mockery::mock(RentalRepositoryInterface::class);
        $maintenanceRepository = \Mockery::mock(MaintenanceRepositoryInterface::class);
        $customerRepository = \Mockery::mock(CustomerRepositoryInterface::class);

        $bikeRepository->shouldReceive('countByStatus')->once()->andReturn([]);
        $bikeRepository->shouldReceive('countActive')->once()->andReturn(0);
        $bikeRepository->shouldReceive('getAverageAge')->once()->andReturn(0.0);
        $rentalRepository->shouldReceive('countActive')->once()->andReturn(0);
        $maintenanceRepository->shouldReceive('countByStatus')->once()->andReturn([]);
        $customerRepository->shouldReceive('count')->once()->andReturn(0);

        $handler = new GetFleetOverviewHandler(
            $bikeRepository,
            $rentalRepository,
            $maintenanceRepository,
            $customerRepository,
        );

        $response = $handler->handle();

        expect($response->fleetSummary['total_bikes'])->toBe(0);
        expect($response->fleetSummary['active_bikes'])->toBe(0);
    });
});

describe('GetFleetOverviewResponse', function () {
    it('can be converted to array', function () {
        $response = new GetFleetOverviewResponse(
            fleetSummary: [
                'total_bikes' => 20,
                'active_bikes' => 15,
                'average_age_years' => 2.0,
                'by_status' => ['available' => 10, 'rented' => 5],
            ],
            rentalsSummary: ['active_rentals' => 5],
            maintenanceSummary: ['by_status' => ['todo' => 2], 'urgent_pending' => 2],
            customersSummary: ['total_customers' => 30, 'with_active_rental' => 5],
        );

        $array = $response->toArray();

        expect($array)->toHaveKeys(['fleet_summary', 'rentals_summary', 'maintenance_summary', 'customers_summary']);
        expect($array['fleet_summary']['total_bikes'])->toBe(20);
    });
});
