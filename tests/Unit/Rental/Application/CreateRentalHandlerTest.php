<?php

declare(strict_types=1);

use Rental\Application\CreateRental\CreateRentalHandler;
use Rental\Application\Services\BikeAvailabilityServiceInterface;
use Rental\Domain\RentalDuration;
use Rental\Domain\RentalRepositoryInterface;
use Customer\Domain\CustomerRepositoryInterface;
use Fleet\Domain\BikeRepositoryInterface;
use Pricing\Domain\Services\PricingCalculator;
use Pricing\Domain\DurationDefinitionRepositoryInterface;

beforeEach(function () {
    // Créer les mocks des repositories
    $rentalRepository = Mockery::mock(RentalRepositoryInterface::class);
    $customerRepository = Mockery::mock(CustomerRepositoryInterface::class);
    $bikeRepository = Mockery::mock(BikeRepositoryInterface::class);
    $availabilityService = Mockery::mock(BikeAvailabilityServiceInterface::class);
    $durationRepository = Mockery::mock(DurationDefinitionRepositoryInterface::class);

    // Créer une vraie instance de PricingCalculator avec ses dépendances mockées
    // (nécessaire car PricingCalculator est final et ne peut être mocké)
    $rateRepository = Mockery::mock(\Pricing\Domain\PricingRateRepositoryInterface::class);
    $durationRepoForPricing = Mockery::mock(\Pricing\Domain\DurationDefinitionRepositoryInterface::class);
    $discountRepository = Mockery::mock(\Pricing\Domain\DiscountRuleRepositoryInterface::class);
    $pricingCalculator = new PricingCalculator(
        $rateRepository,
        $durationRepoForPricing,
        $discountRepository,
    );

    // Créer le handler
    $this->handler = new CreateRentalHandler(
        $rentalRepository,
        $customerRepository,
        $bikeRepository,
        $availabilityService,
        $pricingCalculator,
        $durationRepository,
    );

    // Créer une méthode accessible pour tester la méthode privée
    $reflection = new ReflectionClass($this->handler);
    $this->method = $reflection->getMethod('calculateExpectedReturnDate');
    $this->method->setAccessible(true);
});

test('custom_end_date is used when provided regardless of duration', function () {
    $startDate = new DateTimeImmutable('2026-02-13 00:00:00');
    $customEndDate = new DateTimeImmutable('2026-02-13 23:59:59');

    $result = $this->method->invoke(
        $this->handler,
        $startDate,
        RentalDuration::FULL_DAY,
        $customEndDate
    );

    expect($result->format('Y-m-d H:i:s'))->toBe('2026-02-13 23:59:59');
});

test('TWO_DAYS duration calculates end date at 23:59:59 of second day', function () {
    $startDate = new DateTimeImmutable('2026-02-12 08:00:00');

    $result = $this->method->invoke(
        $this->handler,
        $startDate,
        RentalDuration::TWO_DAYS,
        null
    );

    // TWO_DAYS = du 12 au 13 → retour le 13 à 23:59:59
    expect($result->format('Y-m-d H:i:s'))->toBe('2026-02-13 23:59:59');

    // Vérifier que la période correspond bien à 2 jours pour le calcul de pricing
    $daysDiff = (int) $startDate->diff($result)->days;
    expect($daysDiff)->toBe(1); // Du 12 au 13 = 1 jour de différence

    // Mais la durée utilisée pour le pricing doit être 2 jours
    expect(RentalDuration::TWO_DAYS->days())->toBe(2.0);
});

test('THREE_DAYS duration calculates end date at 23:59:59 of third day', function () {
    $startDate = new DateTimeImmutable('2026-02-12 08:00:00');

    $result = $this->method->invoke(
        $this->handler,
        $startDate,
        RentalDuration::THREE_DAYS,
        null
    );

    // THREE_DAYS = du 12 au 14 → retour le 14 à 23:59:59
    expect($result->format('Y-m-d H:i:s'))->toBe('2026-02-14 23:59:59');

    // Vérifier que la période correspond bien à 3 jours pour le calcul de pricing
    $daysDiff = (int) $startDate->diff($result)->days;
    expect($daysDiff)->toBe(2); // Du 12 au 14 = 2 jours de différence

    // Mais la durée utilisée pour le pricing doit être 3 jours
    expect(RentalDuration::THREE_DAYS->days())->toBe(3.0);
});

test('WEEK duration calculates end date at 23:59:59 of seventh day', function () {
    $startDate = new DateTimeImmutable('2026-02-12 08:00:00');

    $result = $this->method->invoke(
        $this->handler,
        $startDate,
        RentalDuration::WEEK,
        null
    );

    // WEEK (7 days) = du 12 au 18 → retour le 18 à 23:59:59
    expect($result->format('Y-m-d H:i:s'))->toBe('2026-02-18 23:59:59');

    // Vérifier que la période correspond bien à 7 jours pour le calcul de pricing
    $daysDiff = (int) $startDate->diff($result)->days;
    expect($daysDiff)->toBe(6); // Du 12 au 18 = 6 jours de différence

    // Mais la durée utilisée pour le pricing doit être 7 jours (1 semaine)
    expect(RentalDuration::WEEK->days())->toBe(7.0);
});

test('FULL_DAY duration adds 8 hours to start time', function () {
    $startDate = new DateTimeImmutable('2026-02-12 08:00:00');

    $result = $this->method->invoke(
        $this->handler,
        $startDate,
        RentalDuration::FULL_DAY,
        null
    );

    // FULL_DAY = 8 heures → 08:00 + 8h = 16:00
    expect($result->format('Y-m-d H:i:s'))->toBe('2026-02-12 16:00:00');
});

test('HALF_DAY duration adds 4 hours to start time', function () {
    $startDate = new DateTimeImmutable('2026-02-12 08:00:00');

    $result = $this->method->invoke(
        $this->handler,
        $startDate,
        RentalDuration::HALF_DAY,
        null
    );

    // HALF_DAY = 4 heures → 08:00 + 4h = 12:00
    expect($result->format('Y-m-d H:i:s'))->toBe('2026-02-12 12:00:00');
});

test('CUSTOM duration without custom_end_date throws exception', function () {
    $startDate = new DateTimeImmutable('2026-02-12 08:00:00');

    expect(fn () => $this->method->invoke(
        $this->handler,
        $startDate,
        RentalDuration::CUSTOM,
        null
    ))->toThrow(DomainException::class, 'Custom duration requires a custom end date');
});
