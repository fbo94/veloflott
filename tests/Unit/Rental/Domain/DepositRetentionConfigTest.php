<?php

declare(strict_types=1);

namespace Tests\Unit\Rental\Domain;

use Rental\Domain\DamageLevel;
use Rental\Domain\DepositRetentionConfig;
use Rental\Domain\DepositRetentionConfigId;

describe('DepositRetentionConfig', function () {
    describe('createForBike', function () {
        it('creates config for specific bike', function () {
            $id = DepositRetentionConfigId::fromString('config-123');
            $config = DepositRetentionConfig::createForBike(
                id: $id,
                bikeId: 'bike-123',
                minorDamageAmount: 50.0,
                majorDamageAmount: 200.0,
                totalLossAmount: 500.0,
            );

            expect($config->id()->value())->toBe('config-123');
            expect($config->bikeId())->toBe('bike-123');
            expect($config->pricingClassId())->toBeNull();
            expect($config->categoryId())->toBeNull();
            expect($config->minorDamageAmount())->toBe(50.0);
            expect($config->majorDamageAmount())->toBe(200.0);
            expect($config->totalLossAmount())->toBe(500.0);
            expect($config->isForBike())->toBeTrue();
        });
    });

    describe('createForPricingClass', function () {
        it('creates config for pricing class', function () {
            $id = DepositRetentionConfigId::fromString('config-456');
            $config = DepositRetentionConfig::createForPricingClass(
                id: $id,
                pricingClassId: 'class-123',
                minorDamageAmount: 100.0,
                majorDamageAmount: 400.0,
                totalLossAmount: 1000.0,
            );

            expect($config->pricingClassId())->toBe('class-123');
            expect($config->bikeId())->toBeNull();
            expect($config->isForPricingClass())->toBeTrue();
        });
    });

    describe('createForCategory', function () {
        it('creates config for category', function () {
            $id = DepositRetentionConfigId::fromString('config-789');
            $config = DepositRetentionConfig::createForCategory(
                id: $id,
                categoryId: 'cat-123',
                minorDamageAmount: 75.0,
                majorDamageAmount: 300.0,
                totalLossAmount: 750.0,
            );

            expect($config->categoryId())->toBe('cat-123');
            expect($config->bikeId())->toBeNull();
            expect($config->isForCategory())->toBeTrue();
        });
    });

    describe('validation', function () {
        it('throws exception for negative amounts', function () {
            $id = DepositRetentionConfigId::fromString('config-123');

            expect(fn () => DepositRetentionConfig::createForBike(
                id: $id,
                bikeId: 'bike-123',
                minorDamageAmount: -10.0,
                majorDamageAmount: 200.0,
                totalLossAmount: 500.0,
            ))->toThrow(\DomainException::class, 'Damage amounts cannot be negative');
        });

        it('throws exception when minor exceeds major', function () {
            $id = DepositRetentionConfigId::fromString('config-123');

            expect(fn () => DepositRetentionConfig::createForBike(
                id: $id,
                bikeId: 'bike-123',
                minorDamageAmount: 300.0,
                majorDamageAmount: 200.0,
                totalLossAmount: 500.0,
            ))->toThrow(\DomainException::class, 'Minor damage amount cannot exceed major damage amount');
        });

        it('throws exception when major exceeds total', function () {
            $id = DepositRetentionConfigId::fromString('config-123');

            expect(fn () => DepositRetentionConfig::createForBike(
                id: $id,
                bikeId: 'bike-123',
                minorDamageAmount: 50.0,
                majorDamageAmount: 600.0,
                totalLossAmount: 500.0,
            ))->toThrow(\DomainException::class, 'Major damage amount cannot exceed total loss amount');
        });
    });

    describe('getRetentionAmount', function () {
        it('returns correct amount for damage level', function () {
            $id = DepositRetentionConfigId::fromString('config-123');
            $config = DepositRetentionConfig::createForBike(
                id: $id,
                bikeId: 'bike-123',
                minorDamageAmount: 50.0,
                majorDamageAmount: 200.0,
                totalLossAmount: 500.0,
            );

            expect($config->getRetentionAmount(DamageLevel::NONE))->toBe(0.0);
            expect($config->getRetentionAmount(DamageLevel::MINOR))->toBe(50.0);
            expect($config->getRetentionAmount(DamageLevel::MAJOR))->toBe(200.0);
            expect($config->getRetentionAmount(DamageLevel::TOTAL_LOSS))->toBe(500.0);
        });
    });

    describe('update', function () {
        it('updates config amounts', function () {
            $id = DepositRetentionConfigId::fromString('config-123');
            $config = DepositRetentionConfig::createForBike(
                id: $id,
                bikeId: 'bike-123',
                minorDamageAmount: 50.0,
                majorDamageAmount: 200.0,
                totalLossAmount: 500.0,
            );

            $config->update(
                minorDamageAmount: 75.0,
                majorDamageAmount: 300.0,
                totalLossAmount: 750.0,
            );

            expect($config->minorDamageAmount())->toBe(75.0);
            expect($config->majorDamageAmount())->toBe(300.0);
            expect($config->totalLossAmount())->toBe(750.0);
        });
    });

    describe('timestamps', function () {
        it('creates timestamps on instantiation', function () {
            $id = DepositRetentionConfigId::fromString('config-123');
            $config = DepositRetentionConfig::createForBike(
                id: $id,
                bikeId: 'bike-123',
                minorDamageAmount: 50.0,
                majorDamageAmount: 200.0,
                totalLossAmount: 500.0,
            );

            expect($config->createdAt())->toBeInstanceOf(\DateTimeImmutable::class);
            expect($config->updatedAt())->toBeInstanceOf(\DateTimeImmutable::class);
        });
    });
});
