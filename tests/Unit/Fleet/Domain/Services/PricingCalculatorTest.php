<?php

declare(strict_types=1);

use Fleet\Domain\DiscountRule;
use Fleet\Domain\DiscountRuleRepositoryInterface;
use Fleet\Domain\DiscountType;
use Fleet\Domain\DurationDefinition;
use Fleet\Domain\DurationDefinitionRepositoryInterface;
use Fleet\Domain\PricingRate;
use Fleet\Domain\PricingRateRepositoryInterface;
use Fleet\Domain\Services\NoPricingFoundException;
use Fleet\Domain\Services\PricingCalculator;

beforeEach(function () {
    $this->rateRepository = Mockery::mock(PricingRateRepositoryInterface::class);
    $this->durationRepository = Mockery::mock(DurationDefinitionRepositoryInterface::class);
    $this->discountRepository = Mockery::mock(DiscountRuleRepositoryInterface::class);

    $this->calculator = new PricingCalculator(
        $this->rateRepository,
        $this->durationRepository,
        $this->discountRepository
    );
});

afterEach(function () {
    Mockery::close();
});

test('calculates price without discounts', function () {
    $duration = DurationDefinition::create(
        id: 'dur-123',
        code: 'full_day',
        label: 'Journée',
        durationDays: 1,
    );

    $rate = PricingRate::create(
        id: 'rate-123',
        categoryId: 'cat-123',
        pricingClassId: 'class-123',
        durationId: 'dur-123',
        price: 50.00,
    );

    $this->durationRepository
        ->shouldReceive('findById')
        ->with('dur-123')
        ->andReturn($duration);

    $this->rateRepository
        ->shouldReceive('findByDimensions')
        ->with('cat-123', 'class-123', 'dur-123')
        ->andReturn($rate);

    $this->discountRepository
        ->shouldReceive('findApplicableRules')
        ->with('cat-123', 'class-123', 1)
        ->andReturn([]);

    $calculation = $this->calculator->calculate(
        categoryId: 'cat-123',
        pricingClassId: 'class-123',
        durationId: 'dur-123',
        customDays: null,
    );

    expect($calculation->basePrice)->toBe(50.00);
    expect($calculation->finalPrice)->toBe(50.00);
    expect($calculation->days)->toBe(1);
    expect($calculation->pricePerDay)->toBe(50.00);
    expect($calculation->appliedDiscounts)->toBeEmpty();
});

test('calculates price with custom days', function () {
    $duration = DurationDefinition::create(
        id: 'dur-123',
        code: 'custom',
        label: 'Durée personnalisée',
        isCustom: true,
    );

    $rate = PricingRate::create(
        id: 'rate-123',
        categoryId: 'cat-123',
        pricingClassId: 'class-123',
        durationId: 'dur-123',
        price: 50.00,
    );

    $this->durationRepository
        ->shouldReceive('findById')
        ->with('dur-123')
        ->andReturn($duration);

    $this->rateRepository
        ->shouldReceive('findByDimensions')
        ->andReturn($rate);

    $this->discountRepository
        ->shouldReceive('findApplicableRules')
        ->with('cat-123', 'class-123', 5)
        ->andReturn([]);

    $calculation = $this->calculator->calculate(
        categoryId: 'cat-123',
        pricingClassId: 'class-123',
        durationId: 'dur-123',
        customDays: 5,
    );

    expect($calculation->basePrice)->toBe(250.00);  // 50 × 5
    expect($calculation->finalPrice)->toBe(250.00);
    expect($calculation->days)->toBe(5);
});

test('calculates price with percentage discount', function () {
    $duration = DurationDefinition::create(
        id: 'dur-123',
        code: 'full_day',
        label: 'Journée',
        durationDays: 1,
    );

    $rate = PricingRate::create(
        id: 'rate-123',
        categoryId: 'cat-123',
        pricingClassId: 'class-123',
        durationId: 'dur-123',
        price: 50.00,
    );

    $discount = DiscountRule::create(
        id: 'discount-123',
        categoryId: null,
        pricingClassId: null,
        minDays: 1,
        minDurationId: null,
        discountType: DiscountType::PERCENTAGE,
        discountValue: 20.0,  // 20%
        label: 'Réduction 20%',
    );

    $this->durationRepository
        ->shouldReceive('findById')
        ->andReturn($duration);

    $this->rateRepository
        ->shouldReceive('findByDimensions')
        ->andReturn($rate);

    $this->discountRepository
        ->shouldReceive('findApplicableRules')
        ->andReturn([$discount]);

    $calculation = $this->calculator->calculate(
        categoryId: 'cat-123',
        pricingClassId: 'class-123',
        durationId: 'dur-123',
    );

    expect($calculation->basePrice)->toBe(50.00);
    expect($calculation->finalPrice)->toBe(40.00);  // 50 - (50 × 0.20)
    expect($calculation->appliedDiscounts)->toHaveCount(1);
    expect($calculation->appliedDiscounts[0]->amount)->toBe(10.00);
});

test('calculates price with fixed discount', function () {
    $duration = DurationDefinition::create(
        id: 'dur-123',
        code: 'full_day',
        label: 'Journée',
        durationDays: 1,
    );

    $rate = PricingRate::create(
        id: 'rate-123',
        categoryId: 'cat-123',
        pricingClassId: 'class-123',
        durationId: 'dur-123',
        price: 50.00,
    );

    $discount = DiscountRule::create(
        id: 'discount-123',
        categoryId: null,
        pricingClassId: null,
        minDays: 1,
        minDurationId: null,
        discountType: DiscountType::FIXED,
        discountValue: 15.0,  // 15€
        label: 'Réduction 15€',
    );

    $this->durationRepository
        ->shouldReceive('findById')
        ->andReturn($duration);

    $this->rateRepository
        ->shouldReceive('findByDimensions')
        ->andReturn($rate);

    $this->discountRepository
        ->shouldReceive('findApplicableRules')
        ->andReturn([$discount]);

    $calculation = $this->calculator->calculate(
        categoryId: 'cat-123',
        pricingClassId: 'class-123',
        durationId: 'dur-123',
    );

    expect($calculation->basePrice)->toBe(50.00);
    expect($calculation->finalPrice)->toBe(35.00);  // 50 - 15
});

test('throws exception when duration not found', function () {
    $this->durationRepository
        ->shouldReceive('findById')
        ->andReturn(null);

    $this->calculator->calculate(
        categoryId: 'cat-123',
        pricingClassId: 'class-123',
        durationId: 'non-existent',
    );
})->throws(DomainException::class, 'Duration not found');

test('throws exception when no pricing rate found', function () {
    $duration = DurationDefinition::create(
        id: 'dur-123',
        code: 'full_day',
        label: 'Journée',
        durationDays: 1,
    );

    $this->durationRepository
        ->shouldReceive('findById')
        ->andReturn($duration);

    $this->rateRepository
        ->shouldReceive('findByDimensions')
        ->andReturn(null);

    $this->calculator->calculate(
        categoryId: 'cat-123',
        pricingClassId: 'class-123',
        durationId: 'dur-123',
    );
})->throws(NoPricingFoundException::class);

test('throws exception when pricing rate is inactive', function () {
    $duration = DurationDefinition::create(
        id: 'dur-123',
        code: 'full_day',
        label: 'Journée',
        durationDays: 1,
    );

    $rate = PricingRate::create(
        id: 'rate-123',
        categoryId: 'cat-123',
        pricingClassId: 'class-123',
        durationId: 'dur-123',
        price: 50.00,
    );
    $rate->deactivate();

    $this->durationRepository
        ->shouldReceive('findById')
        ->andReturn($duration);

    $this->rateRepository
        ->shouldReceive('findByDimensions')
        ->andReturn($rate);

    $this->calculator->calculate(
        categoryId: 'cat-123',
        pricingClassId: 'class-123',
        durationId: 'dur-123',
    );
})->throws(NoPricingFoundException::class);

test('ensures final price is never negative', function () {
    $duration = DurationDefinition::create(
        id: 'dur-123',
        code: 'full_day',
        label: 'Journée',
        durationDays: 1,
    );

    $rate = PricingRate::create(
        id: 'rate-123',
        categoryId: 'cat-123',
        pricingClassId: 'class-123',
        durationId: 'dur-123',
        price: 50.00,
    );

    // Huge discount that would make price negative
    $discount = DiscountRule::create(
        id: 'discount-123',
        categoryId: null,
        pricingClassId: null,
        minDays: 1,
        minDurationId: null,
        discountType: DiscountType::FIXED,
        discountValue: 100.0,  // 100€ discount on 50€ price
        label: 'Réduction 100€',
    );

    $this->durationRepository
        ->shouldReceive('findById')
        ->andReturn($duration);

    $this->rateRepository
        ->shouldReceive('findByDimensions')
        ->andReturn($rate);

    $this->discountRepository
        ->shouldReceive('findApplicableRules')
        ->andReturn([$discount]);

    $calculation = $this->calculator->calculate(
        categoryId: 'cat-123',
        pricingClassId: 'class-123',
        durationId: 'dur-123',
    );

    expect($calculation->finalPrice)->toBe(0.0);  // Capped at 0
});

test('can calculate quick estimate without discounts', function () {
    $rate = PricingRate::create(
        id: 'rate-123',
        categoryId: 'cat-123',
        pricingClassId: 'class-123',
        durationId: 'dur-123',
        price: 50.00,
    );

    $this->rateRepository
        ->shouldReceive('findByDimensions')
        ->andReturn($rate);

    $estimate = $this->calculator->calculateQuickEstimate(
        categoryId: 'cat-123',
        pricingClassId: 'class-123',
        durationId: 'dur-123',
        days: 3,
    );

    expect($estimate)->toBe(150.00);  // 50 × 3
});
