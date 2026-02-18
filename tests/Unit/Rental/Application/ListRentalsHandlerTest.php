<?php

declare(strict_types=1);

use Customer\Domain\CustomerRepositoryInterface;
use Fleet\Domain\BikeRepositoryInterface;
use Rental\Application\ListRentals\ListRentalsHandler;
use Rental\Application\ListRentals\ListRentalsQuery;
use Rental\Application\ListRentals\ListRentalsResponse;
use Rental\Application\ListRentals\RentalDto;
use Rental\Domain\DepositStatus;
use Rental\Domain\Rental;
use Rental\Domain\RentalDuration;
use Rental\Domain\RentalStatus;

beforeEach(function () {
    $this->customerRepository = Mockery::mock(CustomerRepositoryInterface::class);
    $this->bikeRepository = Mockery::mock(BikeRepositoryInterface::class);
    $this->handler = new ListRentalsHandler(
        $this->customerRepository,
        $this->bikeRepository,
    );
});

afterEach(function () {
    Mockery::close();
});

function createTestRentalForList(
    string $id = 'rental-1',
    string $customerId = 'customer-1',
    RentalStatus $status = RentalStatus::ACTIVE,
): Rental {
    return new Rental(
        id: $id,
        customerId: $customerId,
        startDate: new DateTimeImmutable('2026-02-18 08:00:00'),
        expectedReturnDate: new DateTimeImmutable('2026-02-18 16:00:00'),
        actualReturnDate: null,
        duration: RentalDuration::FULL_DAY,
        depositAmount: 200.0,
        totalAmount: 50.0,
        discountAmount: 0.0,
        taxRate: 20.0,
        taxAmount: 8.33,
        totalWithTax: 50.0,
        status: $status,
        items: [],
        equipments: [],
        depositStatus: DepositStatus::HELD,
        depositRetained: null,
        cancellationReason: null,
        createdAt: new DateTimeImmutable('2026-02-18 07:00:00'),
        updatedAt: new DateTimeImmutable('2026-02-18 08:00:00'),
    );
}

describe('ListRentalsHandler', function () {
    // Note: ListRentalsHandler uses RentalEloquentModel directly with Eloquent queries
    // which cannot be properly mocked in unit tests. These scenarios are tested in feature tests.

    it('returns paginated list of rentals', function () {
        $this->markTestSkipped('Requires database for RentalEloquentModel queries - tested in feature tests');
    });

    it('filters rentals by customer id', function () {
        $this->markTestSkipped('Requires database for RentalEloquentModel queries - tested in feature tests');
    });

    it('filters rentals by status', function () {
        $this->markTestSkipped('Requires database for RentalEloquentModel queries - tested in feature tests');
    });

    it('filters rentals by start date range', function () {
        $this->markTestSkipped('Requires database for RentalEloquentModel queries - tested in feature tests');
    });

    it('filters rentals by end date range', function () {
        $this->markTestSkipped('Requires database for RentalEloquentModel queries - tested in feature tests');
    });

    it('returns empty list when no rentals match filters', function () {
        $this->markTestSkipped('Requires database for RentalEloquentModel queries - tested in feature tests');
    });

    it('paginates results correctly', function () {
        $this->markTestSkipped('Requires database for RentalEloquentModel queries - tested in feature tests');
    });
});

describe('ListRentalsQuery', function () {
    it('holds all filter parameters', function () {
        $query = new ListRentalsQuery(
            customerId: 'customer-1',
            status: 'active',
            startDate: '2026-02-01',
            endDate: '2026-02-28',
            page: 2,
            perPage: 10,
        );

        expect($query->customerId)->toBe('customer-1');
        expect($query->status)->toBe('active');
        expect($query->startDate)->toBe('2026-02-01');
        expect($query->endDate)->toBe('2026-02-28');
        expect($query->page)->toBe(2);
        expect($query->perPage)->toBe(10);
    });

    it('defaults to null filters and first page with 20 items', function () {
        $query = new ListRentalsQuery();

        expect($query->customerId)->toBeNull();
        expect($query->status)->toBeNull();
        expect($query->startDate)->toBeNull();
        expect($query->endDate)->toBeNull();
        expect($query->page)->toBe(1);
        expect($query->perPage)->toBe(20);
    });

    it('allows partial filters', function () {
        $query = new ListRentalsQuery(
            customerId: 'customer-1',
        );

        expect($query->customerId)->toBe('customer-1');
        expect($query->status)->toBeNull();
        expect($query->page)->toBe(1);
    });
});

describe('ListRentalsResponse', function () {
    it('holds paginated rental data', function () {
        $rentalDtos = [];
        $response = new ListRentalsResponse(
            rentals: $rentalDtos,
            total: 0,
            currentPage: 1,
            perPage: 20,
            lastPage: 1,
        );

        expect($response->rentals)->toBe([]);
        expect($response->total)->toBe(0);
        expect($response->currentPage)->toBe(1);
        expect($response->perPage)->toBe(20);
        expect($response->lastPage)->toBe(1);
    });

    it('converts to array with data and meta', function () {
        $rental = createTestRentalForList();
        $rentalDto = RentalDto::fromRental($rental, 'John Doe', ['Bike Model (BIKE-001)']);

        $response = new ListRentalsResponse(
            rentals: [$rentalDto],
            total: 1,
            currentPage: 1,
            perPage: 20,
            lastPage: 1,
        );

        $array = $response->toArray();

        expect($array)->toHaveKeys(['data', 'meta']);
        expect($array['meta'])->toHaveKeys(['total', 'current_page', 'per_page', 'last_page']);
        expect($array['meta']['total'])->toBe(1);
        expect($array['meta']['current_page'])->toBe(1);
        expect($array['data'])->toHaveCount(1);
    });
});

describe('RentalDto', function () {
    it('creates DTO from rental entity', function () {
        $rental = createTestRentalForList(
            id: 'rental-123',
            customerId: 'customer-456',
            status: RentalStatus::ACTIVE,
        );

        $dto = RentalDto::fromRental(
            $rental,
            'John Doe',
            ['Giant Trance (BIKE-001)', 'Specialized Epic (BIKE-002)']
        );

        expect($dto->id)->toBe('rental-123');
        expect($dto->customerId)->toBe('customer-456');
        expect($dto->customerName)->toBe('John Doe');
        expect($dto->status)->toBe('active');
        expect($dto->bikes)->toBe(['Giant Trance (BIKE-001)', 'Specialized Epic (BIKE-002)']);
        expect($dto->duration)->toBe('full_day');
        expect($dto->totalAmount)->toBe(50.0);
        expect($dto->depositAmount)->toBe(200.0);
        expect($dto->depositStatus)->toBe('held');
    });

    it('converts to array', function () {
        $rental = createTestRentalForList();
        $dto = RentalDto::fromRental($rental, 'John Doe', ['Bike 1']);

        $array = $dto->toArray();

        expect($array)->toHaveKeys([
            'id',
            'customer_id',
            'customer_name',
            'start_date',
            'expected_return_date',
            'actual_return_date',
            'bikes',
            'status',
            'duration',
            'total_amount',
            'deposit_amount',
            'deposit_status',
            'deposit_retained',
            'created_at',
        ]);
    });

    it('handles completed rental with actual return date', function () {
        $rental = new Rental(
            id: 'rental-1',
            customerId: 'customer-1',
            startDate: new DateTimeImmutable('2026-02-18 08:00:00'),
            expectedReturnDate: new DateTimeImmutable('2026-02-18 16:00:00'),
            actualReturnDate: new DateTimeImmutable('2026-02-18 15:30:00'),
            duration: RentalDuration::FULL_DAY,
            depositAmount: 200.0,
            totalAmount: 50.0,
            discountAmount: 0.0,
            taxRate: 20.0,
            taxAmount: 8.33,
            totalWithTax: 50.0,
            status: RentalStatus::COMPLETED,
            items: [],
            equipments: [],
            depositStatus: DepositStatus::RELEASED,
            depositRetained: 0.0,
            cancellationReason: null,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );

        $dto = RentalDto::fromRental($rental, 'John Doe', []);

        expect($dto->status)->toBe('completed');
        expect($dto->actualReturnDate)->toBe('2026-02-18 15:30');
        expect($dto->depositStatus)->toBe('released');
        expect($dto->depositRetained)->toBe(0.0);
    });

    it('handles rental with partial deposit retained', function () {
        $rental = new Rental(
            id: 'rental-1',
            customerId: 'customer-1',
            startDate: new DateTimeImmutable('2026-02-18 08:00:00'),
            expectedReturnDate: new DateTimeImmutable('2026-02-18 16:00:00'),
            actualReturnDate: new DateTimeImmutable('2026-02-18 15:30:00'),
            duration: RentalDuration::FULL_DAY,
            depositAmount: 200.0,
            totalAmount: 50.0,
            discountAmount: 0.0,
            taxRate: 20.0,
            taxAmount: 8.33,
            totalWithTax: 50.0,
            status: RentalStatus::COMPLETED,
            items: [],
            equipments: [],
            depositStatus: DepositStatus::PARTIAL,
            depositRetained: 50.0,
            cancellationReason: null,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );

        $dto = RentalDto::fromRental($rental, 'John Doe', []);

        expect($dto->depositStatus)->toBe('partial');
        expect($dto->depositRetained)->toBe(50.0);
    });
});
