<?php

declare(strict_types=1);

use Fleet\Domain\Bike;
use Fleet\Domain\BikeStatus;
use Fleet\Domain\FrameSize;
use Fleet\Domain\PricingTier;
use Fleet\Domain\UnavailabilityReason;

test('can change status from available to maintenance', function () {
    $bike = createTestBike(BikeStatus::AVAILABLE);

    $bike->changeStatusWithReason(BikeStatus::MAINTENANCE);

    expect($bike->status())->toBe(BikeStatus::MAINTENANCE);
});

test('can change status from available to unavailable with reason reserved', function () {
    $bike = createTestBike(BikeStatus::AVAILABLE);

    $bike->changeStatusWithReason(BikeStatus::UNAVAILABLE, UnavailabilityReason::RESERVED);

    expect($bike->status())->toBe(BikeStatus::UNAVAILABLE)
        ->and($bike->unavailabilityReason())->toBe(UnavailabilityReason::RESERVED);
});

test('can change status to unavailable with reason loaned', function () {
    $bike = createTestBike(BikeStatus::AVAILABLE);

    $bike->changeStatusWithReason(BikeStatus::UNAVAILABLE, UnavailabilityReason::LOANED);

    expect($bike->status())->toBe(BikeStatus::UNAVAILABLE)
        ->and($bike->unavailabilityReason())->toBe(UnavailabilityReason::LOANED);
});

test('can change status to unavailable with reason other and comment', function () {
    $bike = createTestBike(BikeStatus::AVAILABLE);

    $bike->changeStatusWithReason(
        BikeStatus::UNAVAILABLE,
        UnavailabilityReason::OTHER,
        'Waiting for inspection'
    );

    expect($bike->status())->toBe(BikeStatus::UNAVAILABLE)
        ->and($bike->unavailabilityReason())->toBe(UnavailabilityReason::OTHER)
        ->and($bike->unavailabilityComment())->toBe('Waiting for inspection');
});

test('cannot change status to unavailable without reason', function () {
    $bike = createTestBike(BikeStatus::AVAILABLE);

    $bike->changeStatusWithReason(BikeStatus::UNAVAILABLE);
})->throws(DomainException::class, 'Unavailability reason is required when marking bike as unavailable');

test('cannot change status when bike is rented', function () {
    $bike = createTestBike(BikeStatus::RENTED);

    $bike->changeStatusWithReason(BikeStatus::MAINTENANCE);
})->throws(DomainException::class, 'Cannot manually change status of a rented bike');

test('can change status from unavailable to available', function () {
    $bike = createTestBike(BikeStatus::UNAVAILABLE);

    $bike->changeStatusWithReason(BikeStatus::AVAILABLE);

    expect($bike->status())->toBe(BikeStatus::AVAILABLE)
        ->and($bike->unavailabilityReason())->toBeNull()
        ->and($bike->unavailabilityComment())->toBeNull();
});

test('can change status from maintenance to available', function () {
    $bike = createTestBike(BikeStatus::MAINTENANCE);

    $bike->changeStatusWithReason(BikeStatus::AVAILABLE);

    expect($bike->status())->toBe(BikeStatus::AVAILABLE);
});

test('cannot change status to rented manually', function () {
    $bike = createTestBike(BikeStatus::AVAILABLE);

    $bike->changeStatusWithReason(BikeStatus::RENTED);
})->throws(DomainException::class, 'Cannot manually set bike status to rented');

test('cannot change status to retired manually', function () {
    $bike = createTestBike(BikeStatus::AVAILABLE);

    $bike->changeStatusWithReason(BikeStatus::RETIRED);
})->throws(DomainException::class, 'Use retire() method to retire a bike');

test('clears unavailability reason when changing from unavailable to other status', function () {
    $bike = createTestBike(BikeStatus::UNAVAILABLE);

    $bike->changeStatusWithReason(BikeStatus::MAINTENANCE);

    expect($bike->status())->toBe(BikeStatus::MAINTENANCE)
        ->and($bike->unavailabilityReason())->toBeNull()
        ->and($bike->unavailabilityComment())->toBeNull();
});

// Helper function to create test bike
function createTestBike(BikeStatus $status = BikeStatus::AVAILABLE): Bike
{
    return new Bike(
        id: '123e4567-e89b-12d3-a456-426614174000',
        qrCodeUuid: '123e4567-e89b-12d3-a456-426614174001',
        internalNumber: 'TEST-001',
        modelId: '123e4567-e89b-12d3-a456-426614174002',
        categoryId: '123e4567-e89b-12d3-a456-426614174003',
        frameSize: FrameSize::fromCentimeters(54),
        status: $status,
        pricingTier: PricingTier::STANDARD,
        pricingClassId: null,
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
