<?php

declare(strict_types=1);

namespace Tests\Unit\Pricing\Domain\Services;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Pricing\Domain\DurationDefinition;
use Pricing\Domain\DurationDefinitionRepositoryInterface;
use Pricing\Domain\PricingRate;
use Pricing\Domain\PricingRateRepositoryInterface;
use Pricing\Domain\Services\PricingValidator;

final class PricingValidatorTest extends TestCase
{
    /** @var MockObject&PricingRateRepositoryInterface */
    private PricingRateRepositoryInterface $rateRepository;
    /** @var MockObject&DurationDefinitionRepositoryInterface */
    private DurationDefinitionRepositoryInterface $durationRepository;
    private PricingValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rateRepository = $this->createMock(PricingRateRepositoryInterface::class);
        $this->durationRepository = $this->createMock(DurationDefinitionRepositoryInterface::class);

        $this->validator = new PricingValidator(
            $this->rateRepository,
            $this->durationRepository
        );
    }

    public function test_cannot_be_rented_when_no_pricing_class(): void
    {
        $result = $this->validator->canBeRented('cat-123', null);

        $this->assertFalse($result);
    }

    public function test_cannot_be_rented_when_no_rates(): void
    {
        $this->rateRepository
            ->method('findByCategoryAndClass')
            ->willReturn([]);

        $result = $this->validator->canBeRented('cat-123', 'class-123');

        $this->assertFalse($result);
    }

    public function test_can_be_rented_when_has_active_rate(): void
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
            ->method('findByCategoryAndClass')
            ->willReturn([$rate]);

        $result = $this->validator->canBeRented('cat-123', 'class-123');

        $this->assertTrue($result);
    }

    public function test_get_available_durations_returns_empty_when_no_rates(): void
    {
        $this->durationRepository
            ->method('findAllActive')
            ->willReturn([]);

        $durations = $this->validator->getAvailableDurations('cat-123', 'class-123');

        $this->assertEmpty($durations);
    }

    public function test_get_available_durations_returns_only_durations_with_rates(): void
    {
        $duration1 = new DurationDefinition(
            id: 'duration-1',
            code: 'full_day',
            label: 'Journée',
            durationHours: null,
            durationDays: 1,
            isCustom: false,
            sortOrder: 1,
            isActive: true
        );

        $duration2 = new DurationDefinition(
            id: 'duration-2',
            code: 'week',
            label: 'Semaine',
            durationHours: null,
            durationDays: 7,
            isCustom: false,
            sortOrder: 2,
            isActive: true
        );

        $rate1 = new PricingRate(
            id: 'rate-1',
            categoryId: 'cat-123',
            pricingClassId: 'class-123',
            durationId: 'duration-1',
            price: 50.0,
            isActive: true
        );

        $this->durationRepository
            ->method('findAllActive')
            ->willReturn([$duration1, $duration2]);

        $this->rateRepository
            ->method('findByDimensions')
            ->willReturnCallback(function ($catId, $classId, $durId) use ($rate1) {
                return $durId === 'duration-1' ? $rate1 : null;
            });

        $durations = $this->validator->getAvailableDurations('cat-123', 'class-123');

        $this->assertCount(1, $durations);
        $this->assertEquals('duration-1', $durations[0]->id());
    }

    public function test_can_delete_pricing_class_when_no_bikes(): void
    {
        $result = $this->validator->canDeletePricingClass('class-123', 0);

        $this->assertTrue($result);
    }

    public function test_cannot_delete_pricing_class_when_bikes_exist(): void
    {
        $result = $this->validator->canDeletePricingClass('class-123', 5);

        $this->assertFalse($result);
    }

    public function test_can_delete_duration_when_no_active_rates(): void
    {
        $rate = new PricingRate(
            id: 'rate-123',
            categoryId: 'cat-123',
            pricingClassId: 'class-123',
            durationId: 'duration-123',
            price: 50.0,
            isActive: false
        );

        $this->rateRepository
            ->method('findAll')
            ->willReturn([$rate]);

        $result = $this->validator->canDeleteDuration('duration-123');

        $this->assertTrue($result);
    }

    public function test_cannot_delete_duration_when_active_rates_exist(): void
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
            ->method('findAll')
            ->willReturn([$rate]);

        $result = $this->validator->canDeleteDuration('duration-123');

        $this->assertFalse($result);
    }

    public function test_can_delete_category_when_no_active_rates(): void
    {
        $this->rateRepository
            ->method('findByCategory')
            ->willReturn([]);

        $result = $this->validator->canDeleteCategory('cat-123');

        $this->assertTrue($result);
    }

    public function test_cannot_delete_category_when_active_rates_exist(): void
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
            ->method('findByCategory')
            ->willReturn([$rate]);

        $result = $this->validator->canDeleteCategory('cat-123');

        $this->assertFalse($result);
    }
}
