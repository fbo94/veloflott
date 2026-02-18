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
use Maintenance\Application\CompleteMaintenance\CompleteMaintenanceCommand;
use Maintenance\Application\CompleteMaintenance\CompleteMaintenanceHandler;
use Maintenance\Application\CompleteMaintenance\CompleteMaintenanceResponse;
use Maintenance\Domain\Exceptions\MaintenanceException;
use Maintenance\Domain\Maintenance;
use Maintenance\Domain\MaintenancePriority;
use Maintenance\Domain\MaintenanceReason;
use Maintenance\Domain\MaintenanceRepositoryInterface;
use Maintenance\Domain\MaintenanceStatus;
use Maintenance\Domain\MaintenanceType;
use Mockery;
use Ramsey\Uuid\Uuid;

describe('CompleteMaintenanceHandler', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('completes maintenance successfully from todo status', function () {
        $maintenanceId = Uuid::uuid4()->toString();
        $bikeId = Uuid::uuid4()->toString();
        $maintenance = createTodoMaintenance($maintenanceId, $bikeId);
        $bike = createBikeForMaintenance($bikeId);

        $maintenanceRepository = Mockery::mock(MaintenanceRepositoryInterface::class);
        $maintenanceRepository->shouldReceive('findById')
            ->with($maintenanceId)
            ->once()
            ->andReturn($maintenance);
        $maintenanceRepository->shouldReceive('save')
            ->once();

        $bikeRepository = Mockery::mock(BikeRepositoryInterface::class);
        $bikeRepository->shouldReceive('findById')
            ->with($bikeId)
            ->once()
            ->andReturn($bike);
        $bikeRepository->shouldReceive('save')
            ->once();

        $handler = new CompleteMaintenanceHandler($maintenanceRepository, $bikeRepository);
        $command = new CompleteMaintenanceCommand(
            maintenanceId: $maintenanceId,
            workDescription: 'Replaced brake pads and adjusted brakes',
            partsReplaced: 'Brake pads x2',
            cost: 5000, // 50 EUR in cents
        );

        $response = $handler->handle($command);

        expect($response)->toBeInstanceOf(CompleteMaintenanceResponse::class);
        expect($response->maintenanceId)->toBe($maintenanceId);
        expect($response->bikeId)->toBe($bikeId);
        expect($response->message)->toBe('Maintenance completed successfully');
    });

    it('completes maintenance successfully from in_progress status', function () {
        $maintenanceId = Uuid::uuid4()->toString();
        $bikeId = Uuid::uuid4()->toString();
        $maintenance = createInProgressMaintenance($maintenanceId, $bikeId);
        $bike = createBikeForMaintenance($bikeId);

        $maintenanceRepository = Mockery::mock(MaintenanceRepositoryInterface::class);
        $maintenanceRepository->shouldReceive('findById')
            ->with($maintenanceId)
            ->once()
            ->andReturn($maintenance);
        $maintenanceRepository->shouldReceive('save')
            ->once();

        $bikeRepository = Mockery::mock(BikeRepositoryInterface::class);
        $bikeRepository->shouldReceive('findById')
            ->with($bikeId)
            ->once()
            ->andReturn($bike);
        $bikeRepository->shouldReceive('save')
            ->once();

        $handler = new CompleteMaintenanceHandler($maintenanceRepository, $bikeRepository);
        $command = new CompleteMaintenanceCommand(
            maintenanceId: $maintenanceId,
            workDescription: 'Full service completed',
        );

        $response = $handler->handle($command);

        expect($response)->toBeInstanceOf(CompleteMaintenanceResponse::class);
        expect($response->maintenanceId)->toBe($maintenanceId);
    });

    it('throws exception when maintenance is already completed', function () {
        $maintenanceId = Uuid::uuid4()->toString();
        $bikeId = Uuid::uuid4()->toString();
        $maintenance = createCompletedMaintenance($maintenanceId, $bikeId);

        $maintenanceRepository = Mockery::mock(MaintenanceRepositoryInterface::class);
        $maintenanceRepository->shouldReceive('findById')
            ->with($maintenanceId)
            ->once()
            ->andReturn($maintenance);

        $bikeRepository = Mockery::mock(BikeRepositoryInterface::class);

        $handler = new CompleteMaintenanceHandler($maintenanceRepository, $bikeRepository);
        $command = new CompleteMaintenanceCommand(
            maintenanceId: $maintenanceId,
            workDescription: 'Trying to complete again',
        );

        expect(fn () => $handler->handle($command))
            ->toThrow(MaintenanceException::class);
    });

    it('completes maintenance with photos', function () {
        $maintenanceId = Uuid::uuid4()->toString();
        $bikeId = Uuid::uuid4()->toString();
        $maintenance = createTodoMaintenance($maintenanceId, $bikeId);
        $bike = createBikeForMaintenance($bikeId);
        $photos = ['before.jpg', 'after.jpg'];

        $maintenanceRepository = Mockery::mock(MaintenanceRepositoryInterface::class);
        $maintenanceRepository->shouldReceive('findById')
            ->with($maintenanceId)
            ->once()
            ->andReturn($maintenance);
        $maintenanceRepository->shouldReceive('save')
            ->once();

        $bikeRepository = Mockery::mock(BikeRepositoryInterface::class);
        $bikeRepository->shouldReceive('findById')
            ->with($bikeId)
            ->once()
            ->andReturn($bike);
        $bikeRepository->shouldReceive('save')
            ->once();

        $handler = new CompleteMaintenanceHandler($maintenanceRepository, $bikeRepository);
        $command = new CompleteMaintenanceCommand(
            maintenanceId: $maintenanceId,
            workDescription: 'Chain replaced',
            partsReplaced: 'Chain',
            cost: 3500,
            photos: $photos,
        );

        $response = $handler->handle($command);

        expect($response)->toBeInstanceOf(CompleteMaintenanceResponse::class);
        expect($maintenance->photos())->toContain('before.jpg');
        expect($maintenance->photos())->toContain('after.jpg');
    });

    it('changes bike status to available after completion', function () {
        $maintenanceId = Uuid::uuid4()->toString();
        $bikeId = Uuid::uuid4()->toString();
        $maintenance = createTodoMaintenance($maintenanceId, $bikeId);
        $bike = createBikeForMaintenance($bikeId);

        $maintenanceRepository = Mockery::mock(MaintenanceRepositoryInterface::class);
        $maintenanceRepository->shouldReceive('findById')
            ->with($maintenanceId)
            ->once()
            ->andReturn($maintenance);
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
                return $savedBike->status() === BikeStatus::AVAILABLE;
            });

        $handler = new CompleteMaintenanceHandler($maintenanceRepository, $bikeRepository);
        $command = new CompleteMaintenanceCommand(
            maintenanceId: $maintenanceId,
            workDescription: 'Maintenance done',
        );

        $handler->handle($command);

        expect($bike->status())->toBe(BikeStatus::AVAILABLE);
    });

    it('updates maintenance status to completed', function () {
        $maintenanceId = Uuid::uuid4()->toString();
        $bikeId = Uuid::uuid4()->toString();
        $maintenance = createTodoMaintenance($maintenanceId, $bikeId);
        $bike = createBikeForMaintenance($bikeId);

        $maintenanceRepository = Mockery::mock(MaintenanceRepositoryInterface::class);
        $maintenanceRepository->shouldReceive('findById')
            ->with($maintenanceId)
            ->once()
            ->andReturn($maintenance);
        $maintenanceRepository->shouldReceive('save')
            ->once()
            ->withArgs(function (Maintenance $savedMaintenance) {
                return $savedMaintenance->status() === MaintenanceStatus::COMPLETED;
            });

        $bikeRepository = Mockery::mock(BikeRepositoryInterface::class);
        $bikeRepository->shouldReceive('findById')
            ->with($bikeId)
            ->once()
            ->andReturn($bike);
        $bikeRepository->shouldReceive('save')
            ->once();

        $handler = new CompleteMaintenanceHandler($maintenanceRepository, $bikeRepository);
        $command = new CompleteMaintenanceCommand(
            maintenanceId: $maintenanceId,
            workDescription: 'Completed work',
        );

        $handler->handle($command);

        expect($maintenance->status())->toBe(MaintenanceStatus::COMPLETED);
    });
});

describe('CompleteMaintenanceCommand', function () {
    it('holds all completion data', function () {
        $command = new CompleteMaintenanceCommand(
            maintenanceId: 'maint-123',
            workDescription: 'Work done',
            partsReplaced: 'Chain, Brake pads',
            cost: 10000,
            photos: ['photo1.jpg', 'photo2.jpg'],
        );

        expect($command->maintenanceId)->toBe('maint-123');
        expect($command->workDescription)->toBe('Work done');
        expect($command->partsReplaced)->toBe('Chain, Brake pads');
        expect($command->cost)->toBe(10000);
        expect($command->photos)->toBe(['photo1.jpg', 'photo2.jpg']);
    });

    it('has default null values for optional fields', function () {
        $command = new CompleteMaintenanceCommand(
            maintenanceId: 'maint-123',
        );

        expect($command->workDescription)->toBeNull();
        expect($command->partsReplaced)->toBeNull();
        expect($command->cost)->toBeNull();
        expect($command->photos)->toBe([]);
    });
});

describe('CompleteMaintenanceResponse', function () {
    it('converts to array', function () {
        $response = new CompleteMaintenanceResponse(
            maintenanceId: 'maint-123',
            bikeId: 'bike-123',
            message: 'Maintenance completed successfully',
        );

        $array = $response->toArray();

        expect($array)->toHaveKeys(['id', 'bike_id', 'message']);
        expect($array['id'])->toBe('maint-123');
        expect($array['bike_id'])->toBe('bike-123');
        expect($array['message'])->toBe('Maintenance completed successfully');
    });
});

function createTodoMaintenance(string $id, string $bikeId): Maintenance
{
    return Maintenance::reconstitute(
        id: $id,
        bikeId: $bikeId,
        type: MaintenanceType::PREVENTIVE,
        reason: MaintenanceReason::FULL_SERVICE_BASIC,
        priority: MaintenancePriority::NORMAL,
        status: MaintenanceStatus::TODO,
        description: 'Test maintenance',
        scheduledAt: new \DateTimeImmutable(),
        startedAt: null,
        completedAt: null,
        workDescription: null,
        partsReplaced: null,
        cost: null,
        photos: [],
        createdAt: new \DateTimeImmutable(),
        updatedAt: new \DateTimeImmutable(),
    );
}

function createInProgressMaintenance(string $id, string $bikeId): Maintenance
{
    return Maintenance::reconstitute(
        id: $id,
        bikeId: $bikeId,
        type: MaintenanceType::CURATIVE,
        reason: MaintenanceReason::BRAKE_PAD_REPLACEMENT,
        priority: MaintenancePriority::URGENT,
        status: MaintenanceStatus::IN_PROGRESS,
        description: 'Urgent brake repair',
        scheduledAt: new \DateTimeImmutable('-1 day'),
        startedAt: new \DateTimeImmutable('-1 hour'),
        completedAt: null,
        workDescription: null,
        partsReplaced: null,
        cost: null,
        photos: [],
        createdAt: new \DateTimeImmutable('-1 day'),
        updatedAt: new \DateTimeImmutable('-1 hour'),
    );
}

function createCompletedMaintenance(string $id, string $bikeId): Maintenance
{
    return Maintenance::reconstitute(
        id: $id,
        bikeId: $bikeId,
        type: MaintenanceType::PREVENTIVE,
        reason: MaintenanceReason::FULL_SERVICE_BASIC,
        priority: MaintenancePriority::NORMAL,
        status: MaintenanceStatus::COMPLETED,
        description: 'Already completed',
        scheduledAt: new \DateTimeImmutable('-1 week'),
        startedAt: new \DateTimeImmutable('-1 week'),
        completedAt: new \DateTimeImmutable('-6 days'),
        workDescription: 'Work was done',
        partsReplaced: null,
        cost: 5000,
        photos: [],
        createdAt: new \DateTimeImmutable('-1 week'),
        updatedAt: new \DateTimeImmutable('-6 days'),
    );
}

function createBikeForMaintenance(string $id): Bike
{
    return new Bike(
        id: $id,
        qrCodeUuid: Uuid::uuid4()->toString(),
        internalNumber: 'BIKE-002',
        modelId: 'model-1',
        categoryId: 'category-1',
        frameSize: FrameSize::fromCentimeters(54),
        status: BikeStatus::MAINTENANCE,
        pricingTier: PricingTier::STANDARD,
        pricingClass: null,
        year: 2023,
        serialNumber: 'SN654321',
        color: 'Red',
        wheelSize: WheelSize::TWENTY_SEVEN_FIVE,
        frontSuspension: 140,
        rearSuspension: 130,
        brakeType: BrakeType::HYDRAULIC_DISC,
        purchasePrice: 2500.00,
        purchaseDate: new \DateTimeImmutable('2023-03-15'),
        notes: null,
        photos: [],
    );
}
