<?php

declare(strict_types=1);

namespace Tests\Unit\Pricing\Domain\Services;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Pricing\Domain\DiscountRule;
use Pricing\Domain\DiscountRuleRepositoryInterface;
use Pricing\Domain\DiscountType;
use Pricing\Domain\DurationDefinition;
use Pricing\Domain\DurationDefinitionRepositoryInterface;
use Pricing\Domain\PricingRate;
use Pricing\Domain\PricingRateRepositoryInterface;
use Pricing\Domain\Services\NoPricingFoundException;
use Pricing\Domain\Services\PricingCalculator;

final class PricingCalculatorTest extends TestCase
{
    /** @var MockObject&PricingRateRepositoryInterface */
    private PricingRateRepositoryInterface $rateRepository;
    /** @var MockObject&DurationDefinitionRepositoryInterface */
    private DurationDefinitionRepositoryInterface $durationRepository;
    /** @var MockObject&DiscountRuleRepositoryInterface */
    private DiscountRuleRepositoryInterface $discountRepository;
    private PricingCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rateRepository = $this->createMock(PricingRateRepositoryInterface::class);
        $this->durationRepository = $this->createMock(DurationDefinitionRepositoryInterface::class);
        $this->discountRepository = $this->createMock(DiscountRuleRepositoryInterface::class);

        $this->calculator = new PricingCalculator(
            $this->rateRepository,
            $this->durationRepository,
            $this->discountRepository
        );
    }

    public function test_calculate_throws_exception_when_duration_not_found(): void
    {
        $this->durationRepository
            ->method('findById')
            ->willReturn(null);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Duration not found');

        $this->calculator->calculate(
            categoryId: 'cat-123',
            pricingClassId: 'class-123',
            durationId: 'duration-123'
        );
    }

    public function test_calculate_throws_exception_when_no_rate_found(): void
    {
        $duration = new DurationDefinition(
            id: 'duration-123',
            code: 'full_day',
            label: 'Journée',
            durationHours: null,
            durationDays: 1,
            isCustom: false,
            sortOrder: 1,
            isActive: true
        );

        $this->durationRepository
            ->method('findById')
            ->willReturn($duration);

        $this->rateRepository
            ->method('findByDimensions')
            ->willReturn(null);

        $this->expectException(NoPricingFoundException::class);

        $this->calculator->calculate(
            categoryId: 'cat-123',
            pricingClassId: 'class-123',
            durationId: 'duration-123'
        );
    }

    public function test_calculate_returns_price_without_discount(): void
    {
        $duration = new DurationDefinition(
            id: 'duration-123',
            code: 'full_day',
            label: 'Journée',
            durationHours: null,
            durationDays: 1,
            isCustom: false,
            sortOrder: 1,
            isActive: true
        );

        $rate = new PricingRate(
            id: 'rate-123',
            categoryId: 'cat-123',
            pricingClassId: 'class-123',
            durationId: 'duration-123',
            price: 50.0,
            isActive: true
        );

        $this->durationRepository
            ->method('findById')
            ->willReturn($duration);

        $this->rateRepository
            ->method('findByDimensions')
            ->willReturn($rate);

        $this->discountRepository
            ->method('findApplicableRules')
            ->willReturn([]);

        $result = $this->calculator->calculate(
            categoryId: 'cat-123',
            pricingClassId: 'class-123',
            durationId: 'duration-123'
        );

        $this->assertEquals(50.0, $result->basePrice);
        $this->assertEquals(50.0, $result->finalPrice);
        $this->assertEquals(1, $result->days);
        $this->assertEquals(50.0, $result->pricePerDay);
        $this->assertEmpty($result->appliedDiscounts);
    }

    public function test_calculate_applies_percentage_discount(): void
    {
        $duration = new DurationDefinition(
            id: 'duration-123',
            code: 'week',
            label: 'Semaine',
            durationHours: null,
            durationDays: 7,
            isCustom: false,
            sortOrder: 5,
            isActive: true
        );

        $rate = new PricingRate(
            id: 'rate-123',
            categoryId: 'cat-123',
            pricingClassId: 'class-123',
            durationId: 'duration-123',
            price: 50.0,
            isActive: true
        );

        $discount = new DiscountRule(
            id: 'discount-123',
            categoryId: null,
            pricingClassId: null,
            minDays: 7,
            minDurationId: null,
            discountType: DiscountType::PERCENTAGE,
            discountValue: 10.0,
            label: 'Réduction 10%',
            description: null,
            isCumulative: false,
            priority: 1,
            isActive: true
        );

        $this->durationRepository
            ->method('findById')
            ->willReturn($duration);

        $this->rateRepository
            ->method('findByDimensions')
            ->willReturn($rate);

        $this->discountRepository
            ->method('findApplicableRules')
            ->willReturn([$discount]);

        $result = $this->calculator->calculate(
            categoryId: 'cat-123',
            pricingClassId: 'class-123',
            durationId: 'duration-123'
        );

        $this->assertEquals(350.0, $result->basePrice); // 50 * 7
        $this->assertEquals(315.0, $result->finalPrice); // 350 - 35 (10%)
        $this->assertEquals(7, $result->days);
        $this->assertCount(1, $result->appliedDiscounts);
        $this->assertEquals(35.0, $result->appliedDiscounts[0]->amount);
    }

    public function test_calculate_applies_fixed_discount(): void
    {
        $duration = new DurationDefinition(
            id: 'duration-123',
            code: 'full_day',
            label: 'Journée',
            durationHours: null,
            durationDays: 1,
            isCustom: false,
            sortOrder: 1,
            isActive: true
        );

        $rate = new PricingRate(
            id: 'rate-123',
            categoryId: 'cat-123',
            pricingClassId: 'class-123',
            durationId: 'duration-123',
            price: 50.0,
            isActive: true
        );

        $discount = new DiscountRule(
            id: 'discount-123',
            categoryId: null,
            pricingClassId: null,
            minDays: 1,
            minDurationId: null,
            discountType: DiscountType::FIXED,
            discountValue: 5.0,
            label: 'Réduction 5€',
            description: null,
            isCumulative: false,
            priority: 1,
            isActive: true
        );

        $this->durationRepository
            ->method('findById')
            ->willReturn($duration);

        $this->rateRepository
            ->method('findByDimensions')
            ->willReturn($rate);

        $this->discountRepository
            ->method('findApplicableRules')
            ->willReturn([$discount]);

        $result = $this->calculator->calculate(
            categoryId: 'cat-123',
            pricingClassId: 'class-123',
            durationId: 'duration-123'
        );

        $this->assertEquals(50.0, $result->basePrice);
        $this->assertEquals(45.0, $result->finalPrice); // 50 - 5
        $this->assertCount(1, $result->appliedDiscounts);
        $this->assertEquals(5.0, $result->appliedDiscounts[0]->amount);
    }

    public function test_calculate_with_custom_days(): void
    {
        $duration = new DurationDefinition(
            id: 'duration-123',
            code: 'custom',
            label: 'Durée personnalisée',
            durationHours: null,
            durationDays: null,
            isCustom: true,
            sortOrder: 99,
            isActive: true
        );

        $rate = new PricingRate(
            id: 'rate-123',
            categoryId: 'cat-123',
            pricingClassId: 'class-123',
            durationId: 'duration-123',
            price: 50.0,
            isActive: true
        );

        $this->durationRepository
            ->method('findById')
            ->willReturn($duration);

        $this->rateRepository
            ->method('findByDimensions')
            ->willReturn($rate);

        $this->discountRepository
            ->method('findApplicableRules')
            ->willReturn([]);

        $result = $this->calculator->calculate(
            categoryId: 'cat-123',
            pricingClassId: 'class-123',
            durationId: 'duration-123',
            customDays: 5
        );

        $this->assertEquals(250.0, $result->basePrice); // 50 * 5
        $this->assertEquals(250.0, $result->finalPrice);
        $this->assertEquals(5, $result->days);
    }

    public function test_calculate_quick_estimate(): void
    {
        $rate = new PricingRate(
            id: 'rate-123',
            categoryId: 'cat-123',
            pricingClassId: 'class-123',
            durationId: 'duration-123',
            price: 50.0,
            isActive: true
        );

        $this->rateRepository
            ->method('findByDimensions')
            ->willReturn($rate);

        $estimate = $this->calculator->calculateQuickEstimate(
            categoryId: 'cat-123',
            pricingClassId: 'class-123',
            durationId: 'duration-123',
            days: 3
        );

        $this->assertEquals(150.0, $estimate); // 50 * 3
    }
}
