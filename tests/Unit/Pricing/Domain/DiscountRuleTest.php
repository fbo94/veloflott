<?php

declare(strict_types=1);

namespace Tests\Unit\Pricing\Domain;

use PHPUnit\Framework\TestCase;
use Pricing\Domain\DiscountRule;
use Pricing\Domain\DiscountType;

final class DiscountRuleTest extends TestCase
{
    public function test_create_percentage_discount(): void
    {
        $discount = DiscountRule::create(
            id: 'discount-123',
            categoryId: null,
            pricingClassId: null,
            minDays: 7,
            minDurationId: null,
            discountType: DiscountType::PERCENTAGE,
            discountValue: 10.0,
            label: 'Réduction 10%',
        );

        $this->assertEquals('discount-123', $discount->id());
        $this->assertEquals(7, $discount->minDays());
        $this->assertEquals(DiscountType::PERCENTAGE, $discount->discountType());
        $this->assertEquals(10.0, $discount->discountValue());
        $this->assertEquals('Réduction 10%', $discount->label());
        $this->assertFalse($discount->isCumulative());
        $this->assertTrue($discount->isActive());
    }

    public function test_create_fixed_discount(): void
    {
        $discount = DiscountRule::create(
            id: 'discount-123',
            categoryId: 'cat-123',
            pricingClassId: 'class-123',
            minDays: 1,
            minDurationId: null,
            discountType: DiscountType::FIXED,
            discountValue: 5.0,
            label: 'Réduction 5€',
        );

        $this->assertEquals(DiscountType::FIXED, $discount->discountType());
        $this->assertEquals(5.0, $discount->discountValue());
        $this->assertEquals('cat-123', $discount->categoryId());
        $this->assertEquals('class-123', $discount->pricingClassId());
    }

    public function test_applies_to_category_when_null(): void
    {
        $discount = DiscountRule::create(
            id: 'discount-123',
            categoryId: null,
            pricingClassId: null,
            minDays: 7,
            minDurationId: null,
            discountType: DiscountType::PERCENTAGE,
            discountValue: 10.0,
            label: 'Test',
        );

        $this->assertTrue($discount->appliesToCategory('any-category'));
        $this->assertTrue($discount->appliesToCategory('another-category'));
    }

    public function test_applies_to_specific_category_only(): void
    {
        $discount = DiscountRule::create(
            id: 'discount-123',
            categoryId: 'cat-123',
            pricingClassId: null,
            minDays: 7,
            minDurationId: null,
            discountType: DiscountType::PERCENTAGE,
            discountValue: 10.0,
            label: 'Test',
        );

        $this->assertTrue($discount->appliesToCategory('cat-123'));
        $this->assertFalse($discount->appliesToCategory('cat-456'));
    }

    public function test_applies_to_pricing_class_when_null(): void
    {
        $discount = DiscountRule::create(
            id: 'discount-123',
            categoryId: null,
            pricingClassId: null,
            minDays: 7,
            minDurationId: null,
            discountType: DiscountType::PERCENTAGE,
            discountValue: 10.0,
            label: 'Test',
        );

        $this->assertTrue($discount->appliesToPricingClass('any-class'));
        $this->assertTrue($discount->appliesToPricingClass('another-class'));
    }

    public function test_applies_to_specific_pricing_class_only(): void
    {
        $discount = DiscountRule::create(
            id: 'discount-123',
            categoryId: null,
            pricingClassId: 'class-123',
            minDays: 7,
            minDurationId: null,
            discountType: DiscountType::PERCENTAGE,
            discountValue: 10.0,
            label: 'Test',
        );

        $this->assertTrue($discount->appliesToPricingClass('class-123'));
        $this->assertFalse($discount->appliesToPricingClass('class-456'));
    }

    public function test_applies_to_days_when_min_days_null(): void
    {
        $discount = DiscountRule::create(
            id: 'discount-123',
            categoryId: null,
            pricingClassId: null,
            minDays: null,
            minDurationId: 'duration-123',
            discountType: DiscountType::PERCENTAGE,
            discountValue: 10.0,
            label: 'Test',
        );

        $this->assertTrue($discount->appliesToDays(1));
        $this->assertTrue($discount->appliesToDays(100));
    }

    public function test_applies_to_days_only_when_meets_minimum(): void
    {
        $discount = DiscountRule::create(
            id: 'discount-123',
            categoryId: null,
            pricingClassId: null,
            minDays: 7,
            minDurationId: null,
            discountType: DiscountType::PERCENTAGE,
            discountValue: 10.0,
            label: 'Test',
        );

        $this->assertFalse($discount->appliesToDays(6));
        $this->assertTrue($discount->appliesToDays(7));
        $this->assertTrue($discount->appliesToDays(8));
    }

    public function test_calculate_percentage_discount(): void
    {
        $discount = DiscountRule::create(
            id: 'discount-123',
            categoryId: null,
            pricingClassId: null,
            minDays: 7,
            minDurationId: null,
            discountType: DiscountType::PERCENTAGE,
            discountValue: 10.0,
            label: 'Test',
        );

        $this->assertEquals(10.0, $discount->calculateDiscount(100.0)); // 10% of 100
        $this->assertEquals(35.0, $discount->calculateDiscount(350.0)); // 10% of 350
    }

    public function test_calculate_fixed_discount(): void
    {
        $discount = DiscountRule::create(
            id: 'discount-123',
            categoryId: null,
            pricingClassId: null,
            minDays: 1,
            minDurationId: null,
            discountType: DiscountType::FIXED,
            discountValue: 5.0,
            label: 'Test',
        );

        $this->assertEquals(5.0, $discount->calculateDiscount(100.0));
        $this->assertEquals(5.0, $discount->calculateDiscount(50.0));
    }

    public function test_calculate_fixed_discount_does_not_exceed_base_price(): void
    {
        $discount = DiscountRule::create(
            id: 'discount-123',
            categoryId: null,
            pricingClassId: null,
            minDays: 1,
            minDurationId: null,
            discountType: DiscountType::FIXED,
            discountValue: 10.0,
            label: 'Test',
        );

        $this->assertEquals(5.0, $discount->calculateDiscount(5.0)); // Cannot exceed base price
    }

    public function test_create_discount_throws_exception_when_no_min_condition(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Discount rule must have either minDays or minDurationId specified');

        DiscountRule::create(
            id: 'discount-123',
            categoryId: null,
            pricingClassId: null,
            minDays: null,
            minDurationId: null,
            discountType: DiscountType::PERCENTAGE,
            discountValue: 10.0,
            label: 'Test',
        );
    }

    public function test_create_discount_throws_exception_when_min_days_less_than_one(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Minimum days must be at least 1');

        DiscountRule::create(
            id: 'discount-123',
            categoryId: null,
            pricingClassId: null,
            minDays: 0,
            minDurationId: null,
            discountType: DiscountType::PERCENTAGE,
            discountValue: 10.0,
            label: 'Test',
        );
    }

    public function test_create_discount_throws_exception_when_value_zero_or_negative(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Discount value must be greater than 0');

        DiscountRule::create(
            id: 'discount-123',
            categoryId: null,
            pricingClassId: null,
            minDays: 7,
            minDurationId: null,
            discountType: DiscountType::PERCENTAGE,
            discountValue: 0.0,
            label: 'Test',
        );
    }

    public function test_create_discount_throws_exception_when_percentage_exceeds_100(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Percentage discount cannot exceed 100%');

        DiscountRule::create(
            id: 'discount-123',
            categoryId: null,
            pricingClassId: null,
            minDays: 7,
            minDurationId: null,
            discountType: DiscountType::PERCENTAGE,
            discountValue: 101.0,
            label: 'Test',
        );
    }

    public function test_create_discount_throws_exception_when_label_empty(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Discount label cannot be empty');

        DiscountRule::create(
            id: 'discount-123',
            categoryId: null,
            pricingClassId: null,
            minDays: 7,
            minDurationId: null,
            discountType: DiscountType::PERCENTAGE,
            discountValue: 10.0,
            label: '',
        );
    }

    public function test_activate_discount(): void
    {
        $discount = DiscountRule::create(
            id: 'discount-123',
            categoryId: null,
            pricingClassId: null,
            minDays: 7,
            minDurationId: null,
            discountType: DiscountType::PERCENTAGE,
            discountValue: 10.0,
            label: 'Test',
        );

        $discount->deactivate();
        $this->assertFalse($discount->isActive());

        $discount->activate();
        $this->assertTrue($discount->isActive());
    }

    public function test_activate_throws_exception_when_already_active(): void
    {
        $discount = DiscountRule::create(
            id: 'discount-123',
            categoryId: null,
            pricingClassId: null,
            minDays: 7,
            minDurationId: null,
            discountType: DiscountType::PERCENTAGE,
            discountValue: 10.0,
            label: 'Test',
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Discount rule is already active');

        $discount->activate();
    }

    public function test_deactivate_discount(): void
    {
        $discount = DiscountRule::create(
            id: 'discount-123',
            categoryId: null,
            pricingClassId: null,
            minDays: 7,
            minDurationId: null,
            discountType: DiscountType::PERCENTAGE,
            discountValue: 10.0,
            label: 'Test',
        );

        $discount->deactivate();
        $this->assertFalse($discount->isActive());
    }

    public function test_deactivate_throws_exception_when_already_inactive(): void
    {
        $discount = DiscountRule::create(
            id: 'discount-123',
            categoryId: null,
            pricingClassId: null,
            minDays: 7,
            minDurationId: null,
            discountType: DiscountType::PERCENTAGE,
            discountValue: 10.0,
            label: 'Test',
        );

        $discount->deactivate();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Discount rule is already inactive');

        $discount->deactivate();
    }
}
