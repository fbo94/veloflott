<?php

declare(strict_types=1);

namespace Tests\Unit\Fleet\Domain;

use Fleet\Domain\Bike;
use Fleet\Domain\BikeStatus;
use Fleet\Domain\BrakeType;
use Fleet\Domain\FrameSize;
use Fleet\Domain\FrameSizeLetter;
use Fleet\Domain\PricingTier;
use Fleet\Domain\RetirementReason;
use Fleet\Domain\UnavailabilityReason;
use Fleet\Domain\WheelSize;

describe('Bike', function () {
    function createBike(array $overrides = []): Bike
    {
        return new Bike(
            id: array_key_exists('id', $overrides) ? $overrides['id'] : 'bike-123',
            qrCodeUuid: array_key_exists('qrCodeUuid', $overrides) ? $overrides['qrCodeUuid'] : 'qr-uuid-456',
            internalNumber: array_key_exists('internalNumber', $overrides) ? $overrides['internalNumber'] : 'BIKE-001',
            modelId: array_key_exists('modelId', $overrides) ? $overrides['modelId'] : 'model-789',
            categoryId: array_key_exists('categoryId', $overrides) ? $overrides['categoryId'] : 'cat-012',
            frameSize: array_key_exists('frameSize', $overrides) ? $overrides['frameSize'] : FrameSize::fromLetter(FrameSizeLetter::M),
            status: array_key_exists('status', $overrides) ? $overrides['status'] : BikeStatus::AVAILABLE,
            pricingTier: array_key_exists('pricingTier', $overrides) ? $overrides['pricingTier'] : PricingTier::STANDARD,
            pricingClass: array_key_exists('pricingClass', $overrides) ? $overrides['pricingClass'] : null,
            year: array_key_exists('year', $overrides) ? $overrides['year'] : 2024,
            serialNumber: array_key_exists('serialNumber', $overrides) ? $overrides['serialNumber'] : 'SN123456',
            color: array_key_exists('color', $overrides) ? $overrides['color'] : 'Red',
            wheelSize: array_key_exists('wheelSize', $overrides) ? $overrides['wheelSize'] : WheelSize::TWENTY_NINE,
            frontSuspension: array_key_exists('frontSuspension', $overrides) ? $overrides['frontSuspension'] : 150,
            rearSuspension: array_key_exists('rearSuspension', $overrides) ? $overrides['rearSuspension'] : 140,
            brakeType: array_key_exists('brakeType', $overrides) ? $overrides['brakeType'] : BrakeType::HYDRAULIC_DISC,
            purchasePrice: array_key_exists('purchasePrice', $overrides) ? $overrides['purchasePrice'] : 3500.00,
            purchaseDate: array_key_exists('purchaseDate', $overrides) ? $overrides['purchaseDate'] : new \DateTimeImmutable('2024-01-15'),
            notes: array_key_exists('notes', $overrides) ? $overrides['notes'] : 'Test notes',
            photos: array_key_exists('photos', $overrides) ? $overrides['photos'] : ['photo1.jpg', 'photo2.jpg'],
        );
    }

    describe('constructor and getters', function () {
        it('creates a bike with all properties', function () {
            $bike = createBike();

            expect($bike->id())->toBe('bike-123');
            expect($bike->qrCodeUuid())->toBe('qr-uuid-456');
            expect($bike->internalNumber())->toBe('BIKE-001');
            expect($bike->modelId())->toBe('model-789');
            expect($bike->categoryId())->toBe('cat-012');
            expect($bike->status())->toBe(BikeStatus::AVAILABLE);
            expect($bike->pricingTier())->toBe(PricingTier::STANDARD);
            expect($bike->year())->toBe(2024);
            expect($bike->serialNumber())->toBe('SN123456');
            expect($bike->color())->toBe('Red');
            expect($bike->wheelSize())->toBe(WheelSize::TWENTY_NINE);
            expect($bike->frontSuspension())->toBe(150);
            expect($bike->rearSuspension())->toBe(140);
            expect($bike->brakeType())->toBe(BrakeType::HYDRAULIC_DISC);
            expect($bike->purchasePrice())->toBe(3500.00);
            expect($bike->notes())->toBe('Test notes');
            expect($bike->photos())->toBe(['photo1.jpg', 'photo2.jpg']);
        });

        it('creates timestamps on instantiation', function () {
            $bike = createBike();

            expect($bike->createdAt())->toBeInstanceOf(\DateTimeImmutable::class);
            expect($bike->updatedAt())->toBeInstanceOf(\DateTimeImmutable::class);
        });
    });

    describe('isRentable', function () {
        it('returns true for available bike', function () {
            $bike = createBike(['status' => BikeStatus::AVAILABLE]);

            expect($bike->isRentable())->toBeTrue();
        });

        it('returns false for rented bike', function () {
            $bike = createBike(['status' => BikeStatus::RENTED]);

            expect($bike->isRentable())->toBeFalse();
        });

        it('returns false for maintenance bike', function () {
            $bike = createBike(['status' => BikeStatus::MAINTENANCE]);

            expect($bike->isRentable())->toBeFalse();
        });

        it('returns false for retired bike', function () {
            $bike = createBike(['status' => BikeStatus::RETIRED]);

            expect($bike->isRentable())->toBeFalse();
        });
    });

    describe('isRetired', function () {
        it('returns true for retired status', function () {
            $bike = createBike(['status' => BikeStatus::RETIRED]);

            expect($bike->isRetired())->toBeTrue();
        });

        it('returns false for other statuses', function () {
            $bike = createBike(['status' => BikeStatus::AVAILABLE]);

            expect($bike->isRetired())->toBeFalse();
        });
    });

    describe('canBeModified', function () {
        it('returns true for available bike', function () {
            $bike = createBike(['status' => BikeStatus::AVAILABLE]);

            expect($bike->canBeModified())->toBeTrue();
        });

        it('returns false for rented bike', function () {
            $bike = createBike(['status' => BikeStatus::RENTED]);

            expect($bike->canBeModified())->toBeFalse();
        });

        it('returns false for retired bike', function () {
            $bike = createBike(['status' => BikeStatus::RETIRED]);

            expect($bike->canBeModified())->toBeFalse();
        });
    });

    describe('canBeRetired', function () {
        it('returns true for available bike', function () {
            $bike = createBike(['status' => BikeStatus::AVAILABLE]);

            expect($bike->canBeRetired())->toBeTrue();
        });

        it('returns false for rented bike', function () {
            $bike = createBike(['status' => BikeStatus::RENTED]);

            expect($bike->canBeRetired())->toBeFalse();
        });
    });

    describe('addPhoto', function () {
        it('adds a photo to the list', function () {
            $bike = createBike(['photos' => ['photo1.jpg']]);

            $bike->addPhoto('photo2.jpg');

            expect($bike->photos())->toBe(['photo1.jpg', 'photo2.jpg']);
        });
    });

    describe('removePhoto', function () {
        it('removes a photo from the list', function () {
            $bike = createBike(['photos' => ['photo1.jpg', 'photo2.jpg']]);

            $bike->removePhoto('photo1.jpg');

            expect($bike->photos())->toBe(['photo2.jpg']);
        });
    });

    describe('updatePhotos', function () {
        it('replaces all photos', function () {
            $bike = createBike(['photos' => ['old1.jpg', 'old2.jpg']]);

            $bike->updatePhotos(['new1.jpg', 'new2.jpg', 'new3.jpg']);

            expect($bike->photos())->toBe(['new1.jpg', 'new2.jpg', 'new3.jpg']);
        });
    });

    describe('changeStatus', function () {
        it('changes status of available bike', function () {
            $bike = createBike(['status' => BikeStatus::AVAILABLE]);

            $bike->changeStatus(BikeStatus::MAINTENANCE);

            expect($bike->status())->toBe(BikeStatus::MAINTENANCE);
        });

        it('throws exception for rented bike', function () {
            $bike = createBike(['status' => BikeStatus::RENTED]);

            expect(fn () => $bike->changeStatus(BikeStatus::MAINTENANCE))
                ->toThrow(\DomainException::class, 'Cannot manually change status of a rented bike');
        });
    });

    describe('changePricingTier', function () {
        it('changes pricing tier', function () {
            $bike = createBike(['pricingTier' => PricingTier::STANDARD]);

            $bike->changePricingTier(PricingTier::PREMIUM);

            expect($bike->pricingTier())->toBe(PricingTier::PREMIUM);
        });
    });

    describe('markAsRented', function () {
        it('marks available bike as rented', function () {
            $bike = createBike(['status' => BikeStatus::AVAILABLE]);

            $bike->markAsRented();

            expect($bike->status())->toBe(BikeStatus::RENTED);
        });

        it('throws exception for non-rentable bike', function () {
            $bike = createBike(['status' => BikeStatus::MAINTENANCE]);

            expect(fn () => $bike->markAsRented())
                ->toThrow(\DomainException::class, 'Bike is not rentable');
        });
    });

    describe('markAsReturned', function () {
        it('marks rented bike as available', function () {
            $bike = createBike(['status' => BikeStatus::RENTED]);

            $bike->markAsReturned();

            expect($bike->status())->toBe(BikeStatus::AVAILABLE);
        });

        it('throws exception for non-rented bike', function () {
            $bike = createBike(['status' => BikeStatus::AVAILABLE]);

            expect(fn () => $bike->markAsReturned())
                ->toThrow(\DomainException::class, 'Bike is not currently rented');
        });
    });

    describe('markAsAvailable', function () {
        it('marks maintenance bike as available', function () {
            $bike = createBike(['status' => BikeStatus::MAINTENANCE]);

            $bike->markAsAvailable();

            expect($bike->status())->toBe(BikeStatus::AVAILABLE);
        });

        it('throws exception for already available bike', function () {
            $bike = createBike(['status' => BikeStatus::AVAILABLE]);

            expect(fn () => $bike->markAsAvailable())
                ->toThrow(\DomainException::class, 'Bike is already available');
        });
    });

    describe('changeStatusWithReason', function () {
        it('changes status to unavailable with reason', function () {
            $bike = createBike(['status' => BikeStatus::AVAILABLE]);

            $bike->changeStatusWithReason(
                BikeStatus::UNAVAILABLE,
                UnavailabilityReason::RESERVED,
                'Reserved for client'
            );

            expect($bike->status())->toBe(BikeStatus::UNAVAILABLE);
            expect($bike->unavailabilityReason())->toBe(UnavailabilityReason::RESERVED);
            expect($bike->unavailabilityComment())->toBe('Reserved for client');
        });

        it('throws exception when setting unavailable without reason', function () {
            $bike = createBike(['status' => BikeStatus::AVAILABLE]);

            expect(fn () => $bike->changeStatusWithReason(BikeStatus::UNAVAILABLE))
                ->toThrow(\DomainException::class, 'Unavailability reason is required');
        });

        it('throws exception when trying to set rented status', function () {
            $bike = createBike(['status' => BikeStatus::AVAILABLE]);

            expect(fn () => $bike->changeStatusWithReason(BikeStatus::RENTED))
                ->toThrow(\DomainException::class, 'Cannot manually set bike status to rented');
        });

        it('throws exception when trying to set retired status', function () {
            $bike = createBike(['status' => BikeStatus::AVAILABLE]);

            expect(fn () => $bike->changeStatusWithReason(BikeStatus::RETIRED))
                ->toThrow(\DomainException::class, 'Use retire() method to retire a bike');
        });

        it('clears unavailability reason when changing to other status', function () {
            $bike = createBike(['status' => BikeStatus::AVAILABLE]);
            $bike->changeStatusWithReason(BikeStatus::UNAVAILABLE, UnavailabilityReason::RESERVED, 'Test');

            $bike->changeStatusWithReason(BikeStatus::MAINTENANCE);

            expect($bike->unavailabilityReason())->toBeNull();
            expect($bike->unavailabilityComment())->toBeNull();
        });
    });

    describe('retire', function () {
        it('retires a bike with reason', function () {
            $bike = createBike(['status' => BikeStatus::AVAILABLE]);

            $bike->retire(RetirementReason::SOLD, 'Sold to customer');

            expect($bike->status())->toBe(BikeStatus::RETIRED);
            expect($bike->retirementReason())->toBe(RetirementReason::SOLD);
            expect($bike->retirementComment())->toBe('Sold to customer');
            expect($bike->retiredAt())->toBeInstanceOf(\DateTimeImmutable::class);
        });

        it('throws exception for rented bike', function () {
            $bike = createBike(['status' => BikeStatus::RENTED]);

            expect(fn () => $bike->retire(RetirementReason::SOLD))
                ->toThrow(\DomainException::class, 'Cannot retire a bike that is currently rented');
        });
    });

    describe('update', function () {
        it('updates bike properties', function () {
            $bike = createBike(['status' => BikeStatus::AVAILABLE]);

            $bike->update(
                modelId: 'new-model',
                categoryId: 'new-category',
                frameSize: FrameSize::fromLetter(FrameSizeLetter::L),
                year: 2025,
                serialNumber: 'NEW-SN',
                color: 'Blue',
                wheelSize: WheelSize::TWENTY_SEVEN_FIVE,
                frontSuspension: 160,
                rearSuspension: 150,
                brakeType: BrakeType::MECHANICAL_DISC,
                purchasePrice: 4000.00,
                purchaseDate: new \DateTimeImmutable('2025-01-01'),
                notes: 'Updated notes',
                pricingClass: null,
            );

            expect($bike->modelId())->toBe('new-model');
            expect($bike->categoryId())->toBe('new-category');
            expect($bike->year())->toBe(2025);
            expect($bike->serialNumber())->toBe('NEW-SN');
            expect($bike->color())->toBe('Blue');
        });

        it('throws exception for non-modifiable bike', function () {
            $bike = createBike(['status' => BikeStatus::RENTED]);

            expect(fn () => $bike->update(
                modelId: 'new-model',
                categoryId: 'new-category',
                frameSize: FrameSize::fromLetter(FrameSizeLetter::L),
                year: 2025,
                serialNumber: 'NEW-SN',
                color: 'Blue',
                wheelSize: null,
                frontSuspension: null,
                rearSuspension: null,
                brakeType: null,
                purchasePrice: null,
                purchaseDate: null,
                notes: null,
                pricingClass: null,
            ))->toThrow(\DomainException::class, 'Cannot modify this bike in its current status');
        });
    });
});
