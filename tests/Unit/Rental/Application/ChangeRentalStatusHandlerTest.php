<?php

declare(strict_types=1);

use Fleet\Domain\Bike;
use Fleet\Domain\BikeRepositoryInterface;
use Fleet\Domain\BikeStatus;
use Fleet\Domain\FrameSize;
use Fleet\Domain\FrameSizeLetter;
use Fleet\Domain\PricingTier;
use Rental\Application\ChangeRentalStatus\ChangeRentalStatusCommand;
use Rental\Application\ChangeRentalStatus\ChangeRentalStatusHandler;
use Rental\Application\ChangeRentalStatus\ChangeRentalStatusResponse;
use Rental\Domain\DepositStatus;
use Rental\Domain\Exceptions\RentalException;
use Rental\Domain\Rental;
use Rental\Domain\RentalDuration;
use Rental\Domain\RentalItem;
use Rental\Domain\RentalRepositoryInterface;
use Rental\Domain\RentalStatus;

beforeEach(function () {
    $this->rentalRepository = Mockery::mock(RentalRepositoryInterface::class);
    $this->bikeRepository = Mockery::mock(BikeRepositoryInterface::class);
    $this->handler = new ChangeRentalStatusHandler(
        $this->rentalRepository,
        $this->bikeRepository,
    );
});

afterEach(function () {
    Mockery::close();
});

function createTestRental(
    string $id = 'rental-1',
    RentalStatus $status = RentalStatus::PENDING,
    array $items = [],
): Rental {
    return new Rental(
        id: $id,
        customerId: 'customer-1',
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
        items: $items,
        equipments: [],
        depositStatus: DepositStatus::HELD,
        depositRetained: null,
        cancellationReason: null,
        createdAt: new DateTimeImmutable(),
        updatedAt: new DateTimeImmutable(),
    );
}

function createTestRentalItem(string $rentalId, string $bikeId): RentalItem
{
    return new RentalItem(
        id: 'item-1',
        rentalId: $rentalId,
        bikeId: $bikeId,
        dailyRate: 50.0,
        quantity: 1,
    );
}

function createRentalTestBike(string $id = 'bike-1', BikeStatus $status = BikeStatus::AVAILABLE): Bike
{
    return new Bike(
        id: $id,
        qrCodeUuid: 'qr-code-uuid',
        internalNumber: 'BIKE-001',
        modelId: 'model-1',
        categoryId: 'category-1',
        frameSize: FrameSize::fromLetter(FrameSizeLetter::M),
        status: $status,
        pricingTier: PricingTier::STANDARD,
        pricingClass: null,
        year: 2024,
        serialNumber: 'SN123456',
        color: 'red',
        wheelSize: null,
        frontSuspension: null,
        rearSuspension: null,
        brakeType: null,
        purchasePrice: 1500.0,
        purchaseDate: null,
        notes: null,
        photos: [],
    );
}

describe('ChangeRentalStatusHandler', function () {
    it('throws exception when rental not found', function () {
        $command = new ChangeRentalStatusCommand(
            rentalId: 'non-existent',
            newStatus: RentalStatus::ACTIVE,
        );

        $this->rentalRepository->shouldReceive('findById')
            ->once()
            ->with('non-existent')
            ->andReturn(null);

        expect(fn () => $this->handler->handle($command))
            ->toThrow(RentalException::class, 'Rental with ID non-existent not found');
    });

    it('returns early when rental is already in requested status', function () {
        $rental = createTestRental(status: RentalStatus::PENDING);

        $command = new ChangeRentalStatusCommand(
            rentalId: 'rental-1',
            newStatus: RentalStatus::PENDING,
        );

        $this->rentalRepository->shouldReceive('findById')
            ->once()
            ->with('rental-1')
            ->andReturn($rental);

        $response = $this->handler->handle($command);

        expect($response)->toBeInstanceOf(ChangeRentalStatusResponse::class);
        expect($response->message)->toBe('Rental is already in the requested status.');
        expect($response->previousStatus)->toBe('pending');
        expect($response->newStatus)->toBe('pending');
    });

    it('transitions from RESERVED to PENDING', function () {
        $rental = createTestRental(status: RentalStatus::RESERVED);

        $command = new ChangeRentalStatusCommand(
            rentalId: 'rental-1',
            newStatus: RentalStatus::PENDING,
        );

        $this->rentalRepository->shouldReceive('findById')
            ->once()
            ->with('rental-1')
            ->andReturn($rental);

        $this->rentalRepository->shouldReceive('save')
            ->once()
            ->with($rental);

        $response = $this->handler->handle($command);

        expect($response)->toBeInstanceOf(ChangeRentalStatusResponse::class);
        expect($response->previousStatus)->toBe('reserved');
        expect($response->newStatus)->toBe('pending');
        expect($response->message)->toBe('Rental confirmed. Customer is ready for check-in.');
    });

    it('transitions from RESERVED to ACTIVE and marks bikes as rented', function () {
        $bikeId = 'bike-1';
        $item = createTestRentalItem('rental-1', $bikeId);
        $rental = createTestRental(status: RentalStatus::RESERVED, items: [$item]);
        $bike = createRentalTestBike($bikeId, BikeStatus::AVAILABLE);

        $command = new ChangeRentalStatusCommand(
            rentalId: 'rental-1',
            newStatus: RentalStatus::ACTIVE,
        );

        $this->rentalRepository->shouldReceive('findById')
            ->once()
            ->with('rental-1')
            ->andReturn($rental);

        $this->rentalRepository->shouldReceive('save')
            ->once()
            ->with($rental);

        $this->bikeRepository->shouldReceive('findById')
            ->once()
            ->with($bikeId)
            ->andReturn($bike);

        $this->bikeRepository->shouldReceive('save')
            ->once()
            ->with($bike);

        $response = $this->handler->handle($command);

        expect($response->previousStatus)->toBe('reserved');
        expect($response->newStatus)->toBe('active');
        expect($response->message)->toBe('Rental started. Bikes are now rented.');
        expect($bike->status())->toBe(BikeStatus::RENTED);
    });

    it('transitions from PENDING to ACTIVE and marks bikes as rented', function () {
        $bikeId = 'bike-1';
        $item = createTestRentalItem('rental-1', $bikeId);
        $rental = createTestRental(status: RentalStatus::PENDING, items: [$item]);
        $bike = createRentalTestBike($bikeId, BikeStatus::AVAILABLE);

        $command = new ChangeRentalStatusCommand(
            rentalId: 'rental-1',
            newStatus: RentalStatus::ACTIVE,
        );

        $this->rentalRepository->shouldReceive('findById')
            ->once()
            ->with('rental-1')
            ->andReturn($rental);

        $this->rentalRepository->shouldReceive('save')
            ->once()
            ->with($rental);

        $this->bikeRepository->shouldReceive('findById')
            ->once()
            ->with($bikeId)
            ->andReturn($bike);

        $this->bikeRepository->shouldReceive('save')
            ->once()
            ->with($bike);

        $response = $this->handler->handle($command);

        expect($response->previousStatus)->toBe('pending');
        expect($response->newStatus)->toBe('active');
        expect($response->message)->toBe('Rental started. Bikes are now rented.');
    });

    it('transitions from ACTIVE to COMPLETED and releases bikes', function () {
        $bikeId = 'bike-1';
        $item = createTestRentalItem('rental-1', $bikeId);
        $rental = createTestRental(status: RentalStatus::ACTIVE, items: [$item]);
        $bike = createRentalTestBike($bikeId, BikeStatus::RENTED);

        $command = new ChangeRentalStatusCommand(
            rentalId: 'rental-1',
            newStatus: RentalStatus::COMPLETED,
        );

        $this->rentalRepository->shouldReceive('findById')
            ->once()
            ->with('rental-1')
            ->andReturn($rental);

        $this->rentalRepository->shouldReceive('save')
            ->once()
            ->with($rental);

        $this->bikeRepository->shouldReceive('findById')
            ->once()
            ->with($bikeId)
            ->andReturn($bike);

        $this->bikeRepository->shouldReceive('save')
            ->once()
            ->with($bike);

        $response = $this->handler->handle($command);

        expect($response->previousStatus)->toBe('active');
        expect($response->newStatus)->toBe('completed');
        expect($response->message)->toBe('Rental completed successfully.');
        expect($bike->status())->toBe(BikeStatus::AVAILABLE);
    });

    it('transitions from RESERVED to CANCELLED', function () {
        $rental = createTestRental(status: RentalStatus::RESERVED);
        $reason = 'Customer requested cancellation';

        $command = new ChangeRentalStatusCommand(
            rentalId: 'rental-1',
            newStatus: RentalStatus::CANCELLED,
            reason: $reason,
        );

        $this->rentalRepository->shouldReceive('findById')
            ->once()
            ->with('rental-1')
            ->andReturn($rental);

        $this->rentalRepository->shouldReceive('save')
            ->once()
            ->with($rental);

        $response = $this->handler->handle($command);

        expect($response->previousStatus)->toBe('reserved');
        expect($response->newStatus)->toBe('cancelled');
        expect($response->message)->toBe('Rental has been cancelled.');
    });

    it('transitions from PENDING to CANCELLED', function () {
        $rental = createTestRental(status: RentalStatus::PENDING);

        $command = new ChangeRentalStatusCommand(
            rentalId: 'rental-1',
            newStatus: RentalStatus::CANCELLED,
            reason: 'No show',
        );

        $this->rentalRepository->shouldReceive('findById')
            ->once()
            ->with('rental-1')
            ->andReturn($rental);

        $this->rentalRepository->shouldReceive('save')
            ->once()
            ->with($rental);

        $response = $this->handler->handle($command);

        expect($response->previousStatus)->toBe('pending');
        expect($response->newStatus)->toBe('cancelled');
        expect($response->message)->toBe('Rental has been cancelled.');
    });

    it('throws exception for invalid transition from COMPLETED', function () {
        $rental = createTestRental(status: RentalStatus::COMPLETED);

        $command = new ChangeRentalStatusCommand(
            rentalId: 'rental-1',
            newStatus: RentalStatus::ACTIVE,
        );

        $this->rentalRepository->shouldReceive('findById')
            ->once()
            ->with('rental-1')
            ->andReturn($rental);

        expect(fn () => $this->handler->handle($command))
            ->toThrow(RentalException::class, "Invalid status transition from 'completed' to 'active'");
    });

    it('throws exception for invalid transition from CANCELLED', function () {
        $rental = createTestRental(status: RentalStatus::CANCELLED);

        $command = new ChangeRentalStatusCommand(
            rentalId: 'rental-1',
            newStatus: RentalStatus::PENDING,
        );

        $this->rentalRepository->shouldReceive('findById')
            ->once()
            ->with('rental-1')
            ->andReturn($rental);

        expect(fn () => $this->handler->handle($command))
            ->toThrow(RentalException::class, "Invalid status transition from 'cancelled' to 'pending'");
    });

    it('throws exception for invalid transition from ACTIVE to PENDING', function () {
        $rental = createTestRental(status: RentalStatus::ACTIVE);

        $command = new ChangeRentalStatusCommand(
            rentalId: 'rental-1',
            newStatus: RentalStatus::PENDING,
        );

        $this->rentalRepository->shouldReceive('findById')
            ->once()
            ->with('rental-1')
            ->andReturn($rental);

        expect(fn () => $this->handler->handle($command))
            ->toThrow(RentalException::class, "Invalid status transition from 'active' to 'pending'");
    });

    it('throws exception for invalid transition from PENDING to RESERVED', function () {
        $rental = createTestRental(status: RentalStatus::PENDING);

        $command = new ChangeRentalStatusCommand(
            rentalId: 'rental-1',
            newStatus: RentalStatus::RESERVED,
        );

        $this->rentalRepository->shouldReceive('findById')
            ->once()
            ->with('rental-1')
            ->andReturn($rental);

        expect(fn () => $this->handler->handle($command))
            ->toThrow(RentalException::class, "Invalid status transition from 'pending' to 'reserved'");
    });

    it('uses default reason when cancelling without reason', function () {
        $rental = createTestRental(status: RentalStatus::PENDING);

        $command = new ChangeRentalStatusCommand(
            rentalId: 'rental-1',
            newStatus: RentalStatus::CANCELLED,
            reason: null,
        );

        $this->rentalRepository->shouldReceive('findById')
            ->once()
            ->with('rental-1')
            ->andReturn($rental);

        $this->rentalRepository->shouldReceive('save')
            ->once()
            ->with($rental);

        $response = $this->handler->handle($command);

        expect($response->newStatus)->toBe('cancelled');
        expect($rental->cancellationReason())->toBe('Status changed manually');
    });

    it('handles multiple bikes when transitioning to ACTIVE', function () {
        $item1 = createTestRentalItem('rental-1', 'bike-1');
        $item2 = createTestRentalItem('rental-1', 'bike-2');
        $rental = createTestRental(status: RentalStatus::PENDING, items: [$item1, $item2]);
        $bike1 = createRentalTestBike('bike-1', BikeStatus::AVAILABLE);
        $bike2 = createRentalTestBike('bike-2', BikeStatus::AVAILABLE);

        $command = new ChangeRentalStatusCommand(
            rentalId: 'rental-1',
            newStatus: RentalStatus::ACTIVE,
        );

        $this->rentalRepository->shouldReceive('findById')
            ->once()
            ->with('rental-1')
            ->andReturn($rental);

        $this->rentalRepository->shouldReceive('save')
            ->once()
            ->with($rental);

        $this->bikeRepository->shouldReceive('findById')
            ->with('bike-1')
            ->andReturn($bike1);

        $this->bikeRepository->shouldReceive('findById')
            ->with('bike-2')
            ->andReturn($bike2);

        $this->bikeRepository->shouldReceive('save')
            ->with($bike1);

        $this->bikeRepository->shouldReceive('save')
            ->with($bike2);

        $response = $this->handler->handle($command);

        expect($response->newStatus)->toBe('active');
        expect($bike1->status())->toBe(BikeStatus::RENTED);
        expect($bike2->status())->toBe(BikeStatus::RENTED);
    });
});

describe('ChangeRentalStatusCommand', function () {
    it('holds rental id, new status and optional reason', function () {
        $command = new ChangeRentalStatusCommand(
            rentalId: 'rental-1',
            newStatus: RentalStatus::CANCELLED,
            reason: 'Test reason',
        );

        expect($command->rentalId)->toBe('rental-1');
        expect($command->newStatus)->toBe(RentalStatus::CANCELLED);
        expect($command->reason)->toBe('Test reason');
    });

    it('defaults reason to null', function () {
        $command = new ChangeRentalStatusCommand(
            rentalId: 'rental-1',
            newStatus: RentalStatus::ACTIVE,
        );

        expect($command->reason)->toBeNull();
    });
});

describe('ChangeRentalStatusResponse', function () {
    it('holds rental id, previous status, new status and message', function () {
        $response = new ChangeRentalStatusResponse(
            rentalId: 'rental-1',
            previousStatus: 'pending',
            newStatus: 'active',
            message: 'Rental started.',
        );

        expect($response->rentalId)->toBe('rental-1');
        expect($response->previousStatus)->toBe('pending');
        expect($response->newStatus)->toBe('active');
        expect($response->message)->toBe('Rental started.');
    });
});
