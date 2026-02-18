<?php

declare(strict_types=1);

namespace Tests\Unit\Maintenance\Application;

use Fleet\Domain\Bike;
use Fleet\Domain\BikeRepositoryInterface;
use Fleet\Domain\BikeStatus;
use Fleet\Domain\BrakeType;
use Fleet\Domain\FrameSize;
use Fleet\Domain\PricingTier;
use Fleet\Domain\WheelSize;
use Maintenance\Application\DeclareMaintenance\DeclareMaintenanceCommand;
use Maintenance\Application\DeclareMaintenance\DeclareMaintenanceHandler;
use Maintenance\Application\DeclareMaintenance\DeclareMaintenanceResponse;
use Maintenance\Domain\Exceptions\MaintenanceException;
use Maintenance\Domain\MaintenanceRepositoryInterface;
use Mockery;
use Ramsey\Uuid\Uuid;

describe('DeclareMaintenanceHandler', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('declares maintenance successfully for an available bike', function () {
        $bikeId = Uuid::uuid4()->toString();
        $bike = createBike($bikeId, BikeStatus::AVAILABLE);

        $maintenanceRepository = Mockery::mock(MaintenanceRepositoryInterface::class);
        $maintenanceRepository->shouldReceive('save')
            ->once();

        $bikeRepository = Mockery::mock(BikeRepositoryInterface::class);
        $bikeRepository->shouldReceive('findById')
            ->with($bikeId)
            ->once()
            ->andReturn($bike);
        $bikeRepository->shouldReceive('save')
            ->once();

        $handler = new DeclareMaintenanceHandler($maintenanceRepository, $bikeRepository);
        $command = new DeclareMaintenanceCommand(
            bikeId: $bikeId,
            type: 'preventive',
            reason: 'full_service_basic',
            priority: 'normal',
            description: 'Annual service',
        );

        $response = $handler->handle($command);

        expect($response)->toBeInstanceOf(DeclareMaintenanceResponse::class);
        expect($response->bikeId)->toBe($bikeId);
        expect($response->message)->toBe('Maintenance declared successfully');
    });

    it('declares maintenance successfully for an unavailable bike', function () {
        $bikeId = Uuid::uuid4()->toString();
        $bike = createBike($bikeId, BikeStatus::UNAVAILABLE);

        $maintenanceRepository = Mockery::mock(MaintenanceRepositoryInterface::class);
        $maintenanceRepository->shouldReceive('save')
            ->once();

        $bikeRepository = Mockery::mock(BikeRepositoryInterface::class);
        $bikeRepository->shouldReceive('findById')
            ->with($bikeId)
            ->once()
            ->andReturn($bike);
        $bikeRepository->shouldReceive('save')
            ->once();

        $handler = new DeclareMaintenanceHandler($maintenanceRepository, $bikeRepository);
        $command = new DeclareMaintenanceCommand(
            bikeId: $bikeId,
            type: 'curative',
            reason: 'brake_bleeding',
            priority: 'urgent',
        );

        $response = $handler->handle($command);

        expect($response)->toBeInstanceOf(DeclareMaintenanceResponse::class);
        expect($response->bikeId)->toBe($bikeId);
    });

    it('throws exception when bike is rented', function () {
        $bikeId = Uuid::uuid4()->toString();
        $bike = createBike($bikeId, BikeStatus::RENTED);

        $maintenanceRepository = Mockery::mock(MaintenanceRepositoryInterface::class);
        $bikeRepository = Mockery::mock(BikeRepositoryInterface::class);
        $bikeRepository->shouldReceive('findById')
            ->with($bikeId)
            ->once()
            ->andReturn($bike);

        $handler = new DeclareMaintenanceHandler($maintenanceRepository, $bikeRepository);
        $command = new DeclareMaintenanceCommand(
            bikeId: $bikeId,
            type: 'preventive',
            reason: 'full_service_basic',
            priority: 'normal',
        );

        expect(fn () => $handler->handle($command))
            ->toThrow(MaintenanceException::class);
    });

    it('throws exception when bike is already in maintenance', function () {
        $bikeId = Uuid::uuid4()->toString();
        $bike = createBike($bikeId, BikeStatus::MAINTENANCE);

        $maintenanceRepository = Mockery::mock(MaintenanceRepositoryInterface::class);
        $bikeRepository = Mockery::mock(BikeRepositoryInterface::class);
        $bikeRepository->shouldReceive('findById')
            ->with($bikeId)
            ->once()
            ->andReturn($bike);

        $handler = new DeclareMaintenanceHandler($maintenanceRepository, $bikeRepository);
        $command = new DeclareMaintenanceCommand(
            bikeId: $bikeId,
            type: 'preventive',
            reason: 'full_service_basic',
            priority: 'normal',
        );

        expect(fn () => $handler->handle($command))
            ->toThrow(MaintenanceException::class);
    });

    it('throws exception when bike is retired', function () {
        $bikeId = Uuid::uuid4()->toString();
        $bike = createBike($bikeId, BikeStatus::RETIRED);

        $maintenanceRepository = Mockery::mock(MaintenanceRepositoryInterface::class);
        $bikeRepository = Mockery::mock(BikeRepositoryInterface::class);
        $bikeRepository->shouldReceive('findById')
            ->with($bikeId)
            ->once()
            ->andReturn($bike);

        $handler = new DeclareMaintenanceHandler($maintenanceRepository, $bikeRepository);
        $command = new DeclareMaintenanceCommand(
            bikeId: $bikeId,
            type: 'preventive',
            reason: 'full_service_basic',
            priority: 'normal',
        );

        expect(fn () => $handler->handle($command))
            ->toThrow(MaintenanceException::class);
    });

    it('declares maintenance with scheduled date', function () {
        $bikeId = Uuid::uuid4()->toString();
        $bike = createBike($bikeId, BikeStatus::AVAILABLE);
        $scheduledAt = new \DateTimeImmutable('+1 week');

        $maintenanceRepository = Mockery::mock(MaintenanceRepositoryInterface::class);
        $maintenanceRepository->shouldReceive('save')
            ->once();

        $bikeRepository = Mockery::mock(BikeRepositoryInterface::class);
        $bikeRepository->shouldReceive('findById')
            ->with($bikeId)
            ->once()
            ->andReturn($bike);
        $bikeRepository->shouldReceive('save')
            ->once();

        $handler = new DeclareMaintenanceHandler($maintenanceRepository, $bikeRepository);
        $command = new DeclareMaintenanceCommand(
            bikeId: $bikeId,
            type: 'preventive',
            reason: 'full_service_advanced',
            priority: 'normal',
            description: 'Scheduled maintenance',
            scheduledAt: $scheduledAt,
        );

        $response = $handler->handle($command);

        expect($response)->toBeInstanceOf(DeclareMaintenanceResponse::class);
        expect($response->bikeId)->toBe($bikeId);
    });

    it('declares maintenance with photos', function () {
        $bikeId = Uuid::uuid4()->toString();
        $bike = createBike($bikeId, BikeStatus::AVAILABLE);
        $photos = ['photo1.jpg', 'photo2.jpg'];

        $maintenanceRepository = Mockery::mock(MaintenanceRepositoryInterface::class);
        $maintenanceRepository->shouldReceive('save')
            ->once();

        $bikeRepository = Mockery::mock(BikeRepositoryInterface::class);
        $bikeRepository->shouldReceive('findById')
            ->with($bikeId)
            ->once()
            ->andReturn($bike);
        $bikeRepository->shouldReceive('save')
            ->once();

        $handler = new DeclareMaintenanceHandler($maintenanceRepository, $bikeRepository);
        $command = new DeclareMaintenanceCommand(
            bikeId: $bikeId,
            type: 'curative',
            reason: 'tire_replacement',
            priority: 'urgent',
            description: 'Flat tire',
            photos: $photos,
        );

        $response = $handler->handle($command);

        expect($response)->toBeInstanceOf(DeclareMaintenanceResponse::class);
    });

    it('changes bike status to maintenance', function () {
        $bikeId = Uuid::uuid4()->toString();
        $bike = createBike($bikeId, BikeStatus::AVAILABLE);

        $maintenanceRepository = Mockery::mock(MaintenanceRepositoryInterface::class);
        $maintenanceRepository->shouldReceive('save')
            ->once();

        $bikeRepository = Mockery::mock(BikeRepositoryInterface::class);
        $bikeRepository->shouldReceive('findById')
            ->with($bikeId)
            ->once()
            ->andReturn($bike);
        $bikeRepository->shouldReceive('save')
            ->once()
            ->withArgs(function (Bike $savedBike) {
                return $savedBike->status() === BikeStatus::MAINTENANCE;
            });

        $handler = new DeclareMaintenanceHandler($maintenanceRepository, $bikeRepository);
        $command = new DeclareMaintenanceCommand(
            bikeId: $bikeId,
            type: 'preventive',
            reason: 'full_service_basic',
            priority: 'normal',
        );

        $handler->handle($command);

        expect($bike->status())->toBe(BikeStatus::MAINTENANCE);
    });
});

describe('DeclareMaintenanceCommand', function () {
    it('holds all maintenance data', function () {
        $scheduledAt = new \DateTimeImmutable();
        $command = new DeclareMaintenanceCommand(
            bikeId: 'bike-123',
            type: 'preventive',
            reason: 'full_service_basic',
            priority: 'urgent',
            description: 'Test description',
            scheduledAt: $scheduledAt,
            photos: ['photo1.jpg'],
        );

        expect($command->bikeId)->toBe('bike-123');
        expect($command->type)->toBe('preventive');
        expect($command->reason)->toBe('full_service_basic');
        expect($command->priority)->toBe('urgent');
        expect($command->description)->toBe('Test description');
        expect($command->scheduledAt)->toBe($scheduledAt);
        expect($command->photos)->toBe(['photo1.jpg']);
    });

    it('has default null values for optional fields', function () {
        $command = new DeclareMaintenanceCommand(
            bikeId: 'bike-123',
            type: 'preventive',
            reason: 'full_service_basic',
            priority: 'normal',
        );

        expect($command->description)->toBeNull();
        expect($command->scheduledAt)->toBeNull();
        expect($command->photos)->toBe([]);
    });
});

describe('DeclareMaintenanceResponse', function () {
    it('converts to array', function () {
        $response = new DeclareMaintenanceResponse(
            maintenanceId: 'maint-123',
            bikeId: 'bike-123',
            message: 'Maintenance declared successfully',
        );

        $array = $response->toArray();

        expect($array)->toHaveKeys(['id', 'bike_id', 'message']);
        expect($array['id'])->toBe('maint-123');
        expect($array['bike_id'])->toBe('bike-123');
        expect($array['message'])->toBe('Maintenance declared successfully');
    });
});

function createBike(string $id, BikeStatus $status): Bike
{
    return new Bike(
        id: $id,
        qrCodeUuid: Uuid::uuid4()->toString(),
        internalNumber: 'BIKE-001',
        modelId: 'model-1',
        categoryId: 'category-1',
        frameSize: FrameSize::fromCentimeters(52),
        status: $status,
        pricingTier: PricingTier::STANDARD,
        pricingClass: null,
        year: 2023,
        serialNumber: 'SN123456',
        color: 'Blue',
        wheelSize: WheelSize::TWENTY_NINE,
        frontSuspension: 120,
        rearSuspension: null,
        brakeType: BrakeType::HYDRAULIC_DISC,
        purchasePrice: 1500.00,
        purchaseDate: new \DateTimeImmutable('2023-01-01'),
        notes: null,
        photos: [],
    );
}
