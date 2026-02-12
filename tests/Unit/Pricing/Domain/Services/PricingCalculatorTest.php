<?php

declare(strict_types=1);

use Pricing\Domain\DiscountRuleRepositoryInterface;
use Pricing\Domain\DurationDefinition;
use Pricing\Domain\DurationDefinitionRepositoryInterface;
use Pricing\Domain\PricingRate;
use Pricing\Domain\PricingRateRepositoryInterface;
use Pricing\Domain\Services\PricingCalculator;

beforeEach(function () {
    // Mock repositories
    $this->rateRepository = Mockery::mock(PricingRateRepositoryInterface::class);
    $this->durationRepository = Mockery::mock(DurationDefinitionRepositoryInterface::class);
    $this->discountRepository = Mockery::mock(DiscountRuleRepositoryInterface::class);

    // Créer le calculator
    $this->calculator = new PricingCalculator(
        $this->rateRepository,
        $this->durationRepository,
        $this->discountRepository,
    );
});

test('TWO_DAYS duration uses correct unit-based calculation', function () {
    // Configuration : Tarif de 30€ pour 2 jours (période complète)
    $categoryId = 'cat-1';
    $pricingClassId = 'class-1';
    $durationId = 'duration-2days';
    $ratePrice = 30.00; // 30€ pour la période de 2 jours

    // Mock duration : TWO_DAYS = 2 jours
    $duration = new DurationDefinition(
        id: $durationId,
        code: 'two_days',
        label: 'Deux jours',
        durationHours: null,
        durationDays: 2, // Unité = 2 jours
        isCustom: false,
        sortOrder: 0,
        isActive: true,
    );

    // Mock pricing rate : 30€ pour 2 jours
    $rate = new PricingRate(
        id: 'rate-1',
        categoryId: $categoryId,
        pricingClassId: $pricingClassId,
        durationId: $durationId,
        price: $ratePrice, // 30€ pour la période complète
        isActive: true,
    );

    // Setup mocks
    $this->durationRepository
        ->shouldReceive('findById')
        ->with($durationId)
        ->andReturn($duration);

    $this->rateRepository
        ->shouldReceive('findByDimensions')
        ->with($categoryId, $pricingClassId, $durationId)
        ->andReturn($rate);

    $this->discountRepository
        ->shouldReceive('findApplicableRules')
        ->andReturn([]);

    // Exécuter le calcul
    $calculation = $this->calculator->calculate(
        categoryId: $categoryId,
        pricingClassId: $pricingClassId,
        durationId: $durationId,
        customDays: null, // Utilise les 2 jours de la durée
    );

    // Vérifier :
    // - Jours loués : 2
    // - Nombre d'unités : 2 / 2 = 1 unité
    // - Prix de base : 30€ × 1 = 30€
    // - Prix par jour : 30€ / 2 = 15€/jour
    expect($calculation->days)->toBe(2);
    expect($calculation->pricePerDay)->toBe(15.00); // 30 / 2
    expect($calculation->basePrice)->toBe(30.00); // 30 × 1
    expect($calculation->finalPrice)->toBe(30.00);
});

test('THREE_DAYS duration uses correct unit-based calculation', function () {
    $categoryId = 'cat-1';
    $pricingClassId = 'class-1';
    $durationId = 'duration-3days';
    $ratePrice = 45.00; // 45€ pour 3 jours

    // Mock duration : THREE_DAYS = 3 jours
    $duration = new DurationDefinition(
        id: $durationId,
        code: 'three_days',
        label: 'Trois jours',
        durationHours: null,
        durationDays: 3, // Unité = 3 jours
        isCustom: false,
        sortOrder: 0,
        isActive: true,
    );

    $rate = new PricingRate(
        id: 'rate-1',
        categoryId: $categoryId,
        pricingClassId: $pricingClassId,
        durationId: $durationId,
        price: $ratePrice, // 45€ pour 3 jours
        isActive: true,
    );

    $this->durationRepository
        ->shouldReceive('findById')
        ->with($durationId)
        ->andReturn($duration);

    $this->rateRepository
        ->shouldReceive('findByDimensions')
        ->with($categoryId, $pricingClassId, $durationId)
        ->andReturn($rate);

    $this->discountRepository
        ->shouldReceive('findApplicableRules')
        ->andReturn([]);

    $calculation = $this->calculator->calculate(
        categoryId: $categoryId,
        pricingClassId: $pricingClassId,
        durationId: $durationId,
        customDays: null,
    );

    // Vérifier :
    // - Jours loués : 3
    // - Nombre d'unités : 3 / 3 = 1 unité
    // - Prix de base : 45€ × 1 = 45€
    // - Prix par jour : 45€ / 3 = 15€/jour
    expect($calculation->days)->toBe(3);
    expect($calculation->pricePerDay)->toBe(15.00); // 45 / 3
    expect($calculation->basePrice)->toBe(45.00); // 45 × 1
    expect($calculation->finalPrice)->toBe(45.00);
});

test('WEEK duration applies weekly rate correctly (not daily multiplication)', function () {
    $categoryId = 'cat-1';
    $pricingClassId = 'class-1';
    $durationId = 'duration-week';
    $weeklyRate = 100.00; // 100€ /semaine (pas 100€/jour !)

    // Mock duration : WEEK = 7 jours
    $duration = new DurationDefinition(
        id: $durationId,
        code: 'week',
        label: 'Semaine',
        durationHours: null,
        durationDays: 7, // Unité = 1 semaine (7 jours)
        isCustom: false,
        sortOrder: 0,
        isActive: true,
    );

    // IMPORTANT : Le tarif est 100€ POUR LA SEMAINE, pas 100€/jour
    $rate = new PricingRate(
        id: 'rate-1',
        categoryId: $categoryId,
        pricingClassId: $pricingClassId,
        durationId: $durationId,
        price: $weeklyRate, // 100€ pour 7 jours
        isActive: true,
    );

    $this->durationRepository
        ->shouldReceive('findById')
        ->with($durationId)
        ->andReturn($duration);

    $this->rateRepository
        ->shouldReceive('findByDimensions')
        ->with($categoryId, $pricingClassId, $durationId)
        ->andReturn($rate);

    $this->discountRepository
        ->shouldReceive('findApplicableRules')
        ->andReturn([]);

    // Test 1 : Location de 7 jours (1 semaine)
    $calculation = $this->calculator->calculate(
        categoryId: $categoryId,
        pricingClassId: $pricingClassId,
        durationId: $durationId,
        customDays: null, // Utilise 7 jours de la durée
    );

    // Vérifier :
    // - Jours loués : 7
    // - Nombre d'unités : 7 / 7 = 1 semaine
    // - Prix de base : 100€ × 1 = 100€ (PAS 100€ × 7 = 700€ !)
    // - Prix par jour : 100€ / 7 ≈ 14.29€/jour
    expect($calculation->days)->toBe(7);
    expect(round($calculation->pricePerDay, 2))->toBe(14.29); // 100 / 7
    expect($calculation->basePrice)->toBe(100.00); // 100 × 1 semaine
    expect($calculation->finalPrice)->toBe(100.00);
});

test('WEEK duration with custom 14 days calculates 2 weeks', function () {
    $categoryId = 'cat-1';
    $pricingClassId = 'class-1';
    $durationId = 'duration-week';
    $weeklyRate = 100.00; // 100€/semaine

    $duration = new DurationDefinition(
        id: $durationId,
        code: 'week',
        label: 'Semaine',
        durationHours: null,
        durationDays: 7,
        isCustom: false,
        sortOrder: 0,
        isActive: true,
    );

    $rate = new PricingRate(
        id: 'rate-1',
        categoryId: $categoryId,
        pricingClassId: $pricingClassId,
        durationId: $durationId,
        price: $weeklyRate,
        isActive: true,
    );

    $this->durationRepository
        ->shouldReceive('findById')
        ->with($durationId)
        ->andReturn($duration);

    $this->rateRepository
        ->shouldReceive('findByDimensions')
        ->with($categoryId, $pricingClassId, $durationId)
        ->andReturn($rate);

    $this->discountRepository
        ->shouldReceive('findApplicableRules')
        ->andReturn([]);

    // Test 2 : Location de 14 jours (2 semaines)
    $calculation = $this->calculator->calculate(
        categoryId: $categoryId,
        pricingClassId: $pricingClassId,
        durationId: $durationId,
        customDays: 14, // 14 jours = 2 semaines
    );

    // Vérifier :
    // - Jours loués : 14
    // - Nombre d'unités : 14 / 7 = 2 semaines
    // - Prix de base : 100€ × 2 = 200€
    expect($calculation->days)->toBe(14);
    expect(round($calculation->pricePerDay, 2))->toBe(14.29); // 100 / 7
    expect($calculation->basePrice)->toBe(200.00); // 100 × 2 semaines
    expect($calculation->finalPrice)->toBe(200.00);
});

test('custom days override duration days in price calculation', function () {
    $categoryId = 'cat-1';
    $pricingClassId = 'class-1';
    $durationId = 'duration-custom';
    $dailyRate = 20.00;
    $customDays = 10; // 10 jours personnalisés

    // Mock duration : CUSTOM (pas de jours définis)
    $duration = new DurationDefinition(
        id: $durationId,
        code: 'custom',
        label: 'Personnalisé',
        durationHours: null,
        durationDays: null,
        isCustom: true,
        sortOrder: 0,
        isActive: true,
    );

    $rate = new PricingRate(
        id: 'rate-1',
        categoryId: $categoryId,
        pricingClassId: $pricingClassId,
        durationId: $durationId,
        price: $dailyRate,
        isActive: true,
    );

    $this->durationRepository
        ->shouldReceive('findById')
        ->with($durationId)
        ->andReturn($duration);

    $this->rateRepository
        ->shouldReceive('findByDimensions')
        ->with($categoryId, $pricingClassId, $durationId)
        ->andReturn($rate);

    $this->discountRepository
        ->shouldReceive('findApplicableRules')
        ->andReturn([]);

    $calculation = $this->calculator->calculate(
        categoryId: $categoryId,
        pricingClassId: $pricingClassId,
        durationId: $durationId,
        customDays: $customDays,
    );

    // Vérifier : Prix = 20€ × 10 jours = 200€
    expect($calculation->days)->toBe(10);
    expect($calculation->pricePerDay)->toBe(20.00);
    expect($calculation->basePrice)->toBe(200.00);
    expect($calculation->finalPrice)->toBe(200.00);
});

test('pricing calculation with discount applies correctly', function () {
    $categoryId = 'cat-1';
    $pricingClassId = 'class-1';
    $durationId = 'duration-week';
    $weeklyRate = 100.00; // 100€/semaine

    // Mock duration : WEEK = 7 jours
    $duration = new DurationDefinition(
        id: $durationId,
        code: 'week',
        label: 'Semaine',
        durationHours: null,
        durationDays: 7,
        isCustom: false,
        sortOrder: 0,
        isActive: true,
    );

    $rate = new PricingRate(
        id: 'rate-1',
        categoryId: $categoryId,
        pricingClassId: $pricingClassId,
        durationId: $durationId,
        price: $weeklyRate, // 100€ pour 1 semaine
        isActive: true,
    );

    // Créer une vraie instance de DiscountRule (classe final)
    $discountRule = new \Pricing\Domain\DiscountRule(
        id: 'discount-1',
        categoryId: $categoryId,
        pricingClassId: $pricingClassId,
        minDays: 7,
        minDurationId: null,
        discountType: \Pricing\Domain\DiscountType::PERCENTAGE,
        discountValue: 15.0, // -15%
        label: 'Réduction semaine',
        description: 'Réduction de 15% pour 1 semaine ou plus',
        isCumulative: false,
        priority: 10,
        isActive: true,
    );

    $this->durationRepository
        ->shouldReceive('findById')
        ->with($durationId)
        ->andReturn($duration);

    $this->rateRepository
        ->shouldReceive('findByDimensions')
        ->with($categoryId, $pricingClassId, $durationId)
        ->andReturn($rate);

    $this->discountRepository
        ->shouldReceive('findApplicableRules')
        ->with($categoryId, $pricingClassId, 7)
        ->andReturn([$discountRule]);

    $calculation = $this->calculator->calculate(
        categoryId: $categoryId,
        pricingClassId: $pricingClassId,
        durationId: $durationId,
        customDays: null,
    );

    // Vérifier :
    // Prix de base = 100€ × 1 semaine = 100€ (PAS 100€ × 7 !)
    // Réduction = 15% de 100€ = 15€
    // Prix final = 100€ - 15€ = 85€
    expect($calculation->days)->toBe(7);
    expect(round($calculation->pricePerDay, 2))->toBe(14.29); // 100 / 7
    expect($calculation->basePrice)->toBe(100.00); // 100 × 1 semaine
    expect($calculation->finalPrice)->toBe(85.00); // 100 - 15
    expect($calculation->totalDiscountAmount())->toBe(15.00);
    expect($calculation->hasDiscounts())->toBeTrue();
});
