<?php

declare(strict_types=1);

namespace Tests\Unit\Pricing\Domain;

use PHPUnit\Framework\TestCase;
use Pricing\Domain\PricingClass;

final class PricingClassTest extends TestCase
{
    public function test_create_pricing_class_successfully(): void
    {
        $pricingClass = PricingClass::create(
            id: 'class-123',
            code: 'premium',
            label: 'Premium',
            description: 'Premium bikes',
            color: '#FFD700',
            sortOrder: 2,
        );

        $this->assertEquals('class-123', $pricingClass->id());
        $this->assertEquals('premium', $pricingClass->code());
        $this->assertEquals('Premium', $pricingClass->label());
        $this->assertEquals('Premium bikes', $pricingClass->description());
        $this->assertEquals('#FFD700', $pricingClass->color());
        $this->assertEquals(2, $pricingClass->sortOrder());
        $this->assertTrue($pricingClass->isActive());
        $this->assertFalse($pricingClass->isDeleted());
    }

    public function test_create_pricing_class_with_minimal_data(): void
    {
        $pricingClass = PricingClass::create(
            id: 'class-123',
            code: 'standard',
            label: 'Standard',
        );

        $this->assertEquals('standard', $pricingClass->code());
        $this->assertEquals('Standard', $pricingClass->label());
        $this->assertNull($pricingClass->description());
        $this->assertNull($pricingClass->color());
        $this->assertEquals(0, $pricingClass->sortOrder());
        $this->assertTrue($pricingClass->isActive());
    }

    public function test_create_pricing_class_throws_exception_when_code_empty(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Pricing class code cannot be empty');

        PricingClass::create(
            id: 'class-123',
            code: '',
            label: 'Test',
        );
    }

    public function test_create_pricing_class_throws_exception_when_code_invalid(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Pricing class code must contain only lowercase letters, numbers and underscores');

        PricingClass::create(
            id: 'class-123',
            code: 'Invalid-Code',
            label: 'Test',
        );
    }

    public function test_create_pricing_class_throws_exception_when_code_too_long(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Pricing class code cannot exceed 50 characters');

        PricingClass::create(
            id: 'class-123',
            code: str_repeat('a', 51),
            label: 'Test',
        );
    }

    public function test_create_pricing_class_throws_exception_when_label_empty(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Pricing class label cannot be empty');

        PricingClass::create(
            id: 'class-123',
            code: 'test',
            label: '',
        );
    }

    public function test_create_pricing_class_throws_exception_when_label_too_long(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Pricing class label cannot exceed 100 characters');

        PricingClass::create(
            id: 'class-123',
            code: 'test',
            label: str_repeat('a', 101),
        );
    }

    public function test_create_pricing_class_throws_exception_when_color_invalid(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Pricing class color must be a valid hex color code');

        PricingClass::create(
            id: 'class-123',
            code: 'test',
            label: 'Test',
            color: 'invalid',
        );
    }

    public function test_update_pricing_class(): void
    {
        $pricingClass = PricingClass::create(
            id: 'class-123',
            code: 'standard',
            label: 'Standard',
        );

        $pricingClass->update(
            label: 'Standard Updated',
            description: 'New description',
            color: '#FFFFFF',
            sortOrder: 5,
        );

        $this->assertEquals('Standard Updated', $pricingClass->label());
        $this->assertEquals('New description', $pricingClass->description());
        $this->assertEquals('#FFFFFF', $pricingClass->color());
        $this->assertEquals(5, $pricingClass->sortOrder());
    }

    public function test_activate_pricing_class(): void
    {
        $pricingClass = PricingClass::create(
            id: 'class-123',
            code: 'standard',
            label: 'Standard',
        );

        $pricingClass->deactivate();
        $this->assertFalse($pricingClass->isActive());

        $pricingClass->activate();
        $this->assertTrue($pricingClass->isActive());
    }

    public function test_activate_throws_exception_when_already_active(): void
    {
        $pricingClass = PricingClass::create(
            id: 'class-123',
            code: 'standard',
            label: 'Standard',
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Pricing class is already active');

        $pricingClass->activate();
    }

    public function test_deactivate_pricing_class(): void
    {
        $pricingClass = PricingClass::create(
            id: 'class-123',
            code: 'standard',
            label: 'Standard',
        );

        $pricingClass->deactivate();
        $this->assertFalse($pricingClass->isActive());
    }

    public function test_deactivate_throws_exception_when_already_inactive(): void
    {
        $pricingClass = PricingClass::create(
            id: 'class-123',
            code: 'standard',
            label: 'Standard',
        );

        $pricingClass->deactivate();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Pricing class is already inactive');

        $pricingClass->deactivate();
    }

    public function test_pricing_class_accepts_valid_hex_colors(): void
    {
        $pricingClass = PricingClass::create(
            id: 'class-123',
            code: 'test',
            label: 'Test',
            color: '#3B82F6',
        );

        $this->assertEquals('#3B82F6', $pricingClass->color());

        $pricingClass->update(
            label: 'Test',
            description: null,
            color: '#ff0000',
            sortOrder: 0,
        );

        $this->assertEquals('#ff0000', $pricingClass->color());
    }
}
