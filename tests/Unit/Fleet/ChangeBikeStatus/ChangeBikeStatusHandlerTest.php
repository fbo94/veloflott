<?php

declare(strict_types=1);

use Fleet\Application\ChangeBikeStatus\ChangeBikeStatusCommand;
use Fleet\Application\ChangeBikeStatus\ChangeBikeStatusHandler;
use Fleet\Application\UpdateBike\BikeNotFoundException;
use Fleet\Domain\Bike;
use Fleet\Domain\BikeRepositoryInterface;
use Fleet\Domain\BikeStatus;
use Fleet\Domain\BikeStatusHistoryRepositoryInterface;
use Fleet\Domain\FrameSize;
use Fleet\Domain\PricingTier;
use Mockery\MockInterface;

beforeEach(function () {
    /** @var MockInterface&BikeRepositoryInterface $repository */
    $repository = Mockery::mock(BikeRepositoryInterface::class);
    $this->repository = $repository;

    /** @var MockInterface&BikeStatusHistoryRepositoryInterface $historyRepository */
    $historyRepository = Mockery::mock(BikeStatusHistoryRepositoryInterface::class);
    $this->historyRepository = $historyRepository;

    $this->handler = new ChangeBikeStatusHandler($this->repository, $this->historyRepository);
});

afterEach(function () {
    Mockery::close();
});

test('can change bike status to maintenance', function () {
    $bikeId = '123e4567-e89b-12d3-a456-426614174000';
    $bike = createMockBike($bikeId, BikeStatus::AVAILABLE);

    $this->repository->shouldReceive('findById')
        ->with($bikeId)
        ->once()
        ->andReturn($bike);

    $this->repository->shouldReceive('save')
        ->once()
        ->with($bike);

    $this->historyRepository->shouldReceive('save')
        ->once();

    $command = new ChangeBikeStatusCommand(
        bikeId: $bikeId,
        status: 'maintenance',
    );

    $response = $this->handler->handle($command);

    expect($response->id)->toBe($bikeId)
        ->and($response->message)->toBe('Bike status changed successfully');
});

test('can change bike status to unavailable with reason', function () {
    $bikeId = '123e4567-e89b-12d3-a456-426614174000';
    $bike = createMockBike($bikeId, BikeStatus::AVAILABLE);

    $this->repository->shouldReceive('findById')
        ->with($bikeId)
        ->once()
        ->andReturn($bike);

    $this->repository->shouldReceive('save')
        ->once()
        ->with($bike);

    $this->historyRepository->shouldReceive('save')
        ->once();

    $command = new ChangeBikeStatusCommand(
        bikeId: $bikeId,
        status: 'unavailable',
        unavailabilityReason: 'reserved',
    );

    $response = $this->handler->handle($command);

    expect($response->id)->toBe($bikeId);
});

test('can change status to unavailable with other reason and comment', function () {
    $bikeId = '123e4567-e89b-12d3-a456-426614174000';
    $bike = createMockBike($bikeId, BikeStatus::AVAILABLE);

    $this->repository->shouldReceive('findById')
        ->with($bikeId)
        ->once()
        ->andReturn($bike);

    $this->repository->shouldReceive('save')
        ->once()
        ->with($bike);

    $this->historyRepository->shouldReceive('save')
        ->once();

    $command = new ChangeBikeStatusCommand(
        bikeId: $bikeId,
        status: 'unavailable',
        unavailabilityReason: 'other',
        unavailabilityComment: 'Reserved for VIP client',
    );

    $response = $this->handler->handle($command);

    expect($response->id)->toBe($bikeId);
});

test('throws exception when bike not found', function () {
    $bikeId = '123e4567-e89b-12d3-a456-426614174000';

    $this->repository->shouldReceive('findById')
        ->with($bikeId)
        ->once()
        ->andReturn(null);

    $command = new ChangeBikeStatusCommand(
        bikeId: $bikeId,
        status: 'maintenance',
    );

    $this->handler->handle($command);
})->throws(BikeNotFoundException::class);

test('throws exception when domain rule violated', function () {
    $bikeId = '123e4567-e89b-12d3-a456-426614174000';
    $bike = createMockBike($bikeId, BikeStatus::RENTED);

    $this->repository->shouldReceive('findById')
        ->with($bikeId)
        ->once()
        ->andReturn($bike);

    $command = new ChangeBikeStatusCommand(
        bikeId: $bikeId,
        status: 'maintenance',
    );

    $this->handler->handle($command);
})->throws(InvalidArgumentException::class, 'Cannot manually change status of a rented bike');

test('can change status back to available', function () {
    $bikeId = '123e4567-e89b-12d3-a456-426614174000';
    $bike = createMockBike($bikeId, BikeStatus::MAINTENANCE);

    $this->repository->shouldReceive('findById')
        ->with($bikeId)
        ->once()
        ->andReturn($bike);

    $this->repository->shouldReceive('save')
        ->once()
        ->with($bike);

    $this->historyRepository->shouldReceive('save')
        ->once();

    $command = new ChangeBikeStatusCommand(
        bikeId: $bikeId,
        status: 'available',
    );

    $response = $this->handler->handle($command);

    expect($response->id)->toBe($bikeId);
});

// Helper function
function createMockBike(string $id, BikeStatus $status): Bike
{
    return new Bike(
        id: $id,
        qrCodeUuid: '123e4567-e89b-12d3-a456-426614174001',
        internalNumber: 'TEST-001',
        modelId: '123e4567-e89b-12d3-a456-426614174002',
        categoryId: '123e4567-e89b-12d3-a456-426614174003',
        frameSize: FrameSize::fromCentimeters(54),
        status: $status,
        pricingTier: PricingTier::STANDARD,
        pricingClass: null,
        year: 2024,
        serialNumber: null,
        color: null,
        wheelSize: null,
        frontSuspension: null,
        rearSuspension: null,
        brakeType: null,
        purchasePrice: null,
        purchaseDate: null,
        notes: null,
        photos: [],
    );
}
