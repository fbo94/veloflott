<?php

declare(strict_types=1);

namespace Tests\Unit\Dashboard\Application;

use Dashboard\Application\GetRevenueKpi\GetRevenueKpiHandler;
use Dashboard\Application\GetRevenueKpi\GetRevenueKpiQuery;
use Dashboard\Application\GetRevenueKpi\GetRevenueKpiResponse;
use Fleet\Domain\BikeRepositoryInterface;
use Rental\Domain\RentalRepositoryInterface;

describe('GetRevenueKpiHandler', function () {
    it('returns revenue KPIs with default 30-day period when no dates provided', function () {
        $bikeRepository = \Mockery::mock(BikeRepositoryInterface::class);
        $rentalRepository = \Mockery::mock(RentalRepositoryInterface::class);

        $bikeRepository->shouldReceive('countActive')
            ->once()
            ->andReturn(50);

        $rentalRepository->shouldReceive('countByPeriod')
            ->once()
            ->andReturn(100);

        $rentalRepository->shouldReceive('sumRevenueByPeriod')
            ->once()
            ->andReturn(500000); // 5000.00 EUR in cents

        $handler = new GetRevenueKpiHandler($bikeRepository, $rentalRepository);
        $query = new GetRevenueKpiQuery();

        $response = $handler->handle($query);

        expect($response)->toBeInstanceOf(GetRevenueKpiResponse::class);
        expect($response->totalRevenueCents)->toBe(500000);
        expect($response->totalRevenueFormatted)->toBe('5 000.00 EUR');
        expect($response->revpavCents)->toBe(10000); // 500000 / 50 = 10000 cents per bike
        expect($response->avgRevenuePerRentalCents)->toBe(5000); // 500000 / 100 = 5000 cents per rental
        expect($response->rentalCount)->toBe(100);
        expect($response->activeBikes)->toBe(50);
        expect($response->period)->toHaveKeys(['from', 'to', 'days']);
    });

    it('returns revenue KPIs with custom date range', function () {
        $bikeRepository = \Mockery::mock(BikeRepositoryInterface::class);
        $rentalRepository = \Mockery::mock(RentalRepositoryInterface::class);

        $dateFrom = new \DateTimeImmutable('2024-01-01');
        $dateTo = new \DateTimeImmutable('2024-01-15');

        $bikeRepository->shouldReceive('countActive')
            ->once()
            ->andReturn(25);

        $rentalRepository->shouldReceive('countByPeriod')
            ->once()
            ->with(
                \Mockery::on(fn ($d) => $d->format('Y-m-d') === '2024-01-01'),
                \Mockery::on(fn ($d) => $d->format('Y-m-d') === '2024-01-15')
            )
            ->andReturn(50);

        $rentalRepository->shouldReceive('sumRevenueByPeriod')
            ->once()
            ->with(
                \Mockery::on(fn ($d) => $d->format('Y-m-d') === '2024-01-01'),
                \Mockery::on(fn ($d) => $d->format('Y-m-d') === '2024-01-15')
            )
            ->andReturn(250000); // 2500.00 EUR in cents

        $handler = new GetRevenueKpiHandler($bikeRepository, $rentalRepository);
        $query = new GetRevenueKpiQuery($dateFrom, $dateTo);

        $response = $handler->handle($query);

        expect($response->period['from'])->toBe('2024-01-01');
        expect($response->period['to'])->toBe('2024-01-15');
        expect($response->totalRevenueCents)->toBe(250000);
        expect($response->revpavCents)->toBe(10000); // 250000 / 25
        expect($response->avgRevenuePerRentalCents)->toBe(5000); // 250000 / 50
    });

    it('handles zero active bikes correctly', function () {
        $bikeRepository = \Mockery::mock(BikeRepositoryInterface::class);
        $rentalRepository = \Mockery::mock(RentalRepositoryInterface::class);

        $bikeRepository->shouldReceive('countActive')
            ->once()
            ->andReturn(0);

        $rentalRepository->shouldReceive('countByPeriod')
            ->once()
            ->andReturn(0);

        $rentalRepository->shouldReceive('sumRevenueByPeriod')
            ->once()
            ->andReturn(0);

        $handler = new GetRevenueKpiHandler($bikeRepository, $rentalRepository);
        $query = new GetRevenueKpiQuery();

        $response = $handler->handle($query);

        expect($response->revpavCents)->toBe(0);
        expect($response->avgRevenuePerRentalCents)->toBe(0);
        expect($response->activeBikes)->toBe(0);
    });

    it('handles zero rentals correctly', function () {
        $bikeRepository = \Mockery::mock(BikeRepositoryInterface::class);
        $rentalRepository = \Mockery::mock(RentalRepositoryInterface::class);

        $bikeRepository->shouldReceive('countActive')
            ->once()
            ->andReturn(20);

        $rentalRepository->shouldReceive('countByPeriod')
            ->once()
            ->andReturn(0);

        $rentalRepository->shouldReceive('sumRevenueByPeriod')
            ->once()
            ->andReturn(0);

        $handler = new GetRevenueKpiHandler($bikeRepository, $rentalRepository);
        $query = new GetRevenueKpiQuery();

        $response = $handler->handle($query);

        expect($response->totalRevenueCents)->toBe(0);
        expect($response->revpavCents)->toBe(0);
        expect($response->avgRevenuePerRentalCents)->toBe(0);
        expect($response->rentalCount)->toBe(0);
        expect($response->activeBikes)->toBe(20);
    });

    it('calculates RevPAV correctly with rounding', function () {
        $bikeRepository = \Mockery::mock(BikeRepositoryInterface::class);
        $rentalRepository = \Mockery::mock(RentalRepositoryInterface::class);

        $bikeRepository->shouldReceive('countActive')
            ->once()
            ->andReturn(3);

        $rentalRepository->shouldReceive('countByPeriod')
            ->once()
            ->andReturn(7);

        // 10000 / 3 = 3333.33... should round to 3333
        // 10000 / 7 = 1428.57... should round to 1429
        $rentalRepository->shouldReceive('sumRevenueByPeriod')
            ->once()
            ->andReturn(10000);

        $handler = new GetRevenueKpiHandler($bikeRepository, $rentalRepository);
        $query = new GetRevenueKpiQuery();

        $response = $handler->handle($query);

        expect($response->revpavCents)->toBe(3333);
        expect($response->avgRevenuePerRentalCents)->toBe(1429);
    });
});

describe('GetRevenueKpiResponse', function () {
    it('can be converted to array', function () {
        $response = new GetRevenueKpiResponse(
            period: [
                'from' => '2024-01-01',
                'to' => '2024-01-31',
                'days' => 31,
            ],
            totalRevenueCents: 500000,
            totalRevenueFormatted: '5 000.00 EUR',
            revpavCents: 10000,
            avgRevenuePerRentalCents: 5000,
            rentalCount: 100,
            activeBikes: 50,
        );

        $array = $response->toArray();

        expect($array)->toHaveKeys([
            'period',
            'total_revenue_cents',
            'total_revenue_formatted',
            'revpav_cents',
            'avg_revenue_per_rental_cents',
            'rental_count',
            'active_bikes',
        ]);
        expect($array['total_revenue_cents'])->toBe(500000);
        expect($array['revpav_cents'])->toBe(10000);
        expect($array['rental_count'])->toBe(100);
        expect($array['active_bikes'])->toBe(50);
    });
});
