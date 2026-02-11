<?php

declare(strict_types=1);

namespace Tests\Unit\Pricing\Application;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Pricing\Application\BulkUpdatePricingRates\BulkUpdatePricingRatesCommand;
use Pricing\Application\BulkUpdatePricingRates\BulkUpdatePricingRatesHandler;
use Pricing\Domain\PricingRate;
use Pricing\Domain\PricingRateRepositoryInterface;

final class BulkUpdatePricingRatesHandlerTest extends TestCase
{
    /** @var MockObject&PricingRateRepositoryInterface */
    private PricingRateRepositoryInterface $repository;

    private BulkUpdatePricingRatesHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(PricingRateRepositoryInterface::class);
        $this->handler = new BulkUpdatePricingRatesHandler($this->repository);
    }

    public function test_creates_new_rate_when_not_exists(): void
    {
        $command = new BulkUpdatePricingRatesCommand(
            rates: [
                [
                    'category_id' => 'cat-123',
                    'pricing_class_id' => 'class-123',
                    'duration_id' => 'duration-123',
                    'price' => 50.0,
                ],
            ],
        );

        $this->repository->expects($this->once())
            ->method('findByDimensions')
            ->with('cat-123', 'class-123', 'duration-123')
            ->willReturn(null);

        $this->repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (PricingRate $rate) {
                return $rate->price() === 50.0
                    && $rate->isActive() === true
                    && $rate->categoryId() === 'cat-123';
            }));

        $result = $this->handler->handle($command);

        $this->assertEquals(1, $result['created']);
        $this->assertEquals(0, $result['updated']);
    }

    public function test_updates_existing_rate(): void
    {
        $existingRate = new PricingRate(
            id: 'rate-123',
            categoryId: 'cat-123',
            pricingClassId: 'class-123',
            durationId: 'duration-123',
            price: 30.0,
            isActive: true,
        );

        $command = new BulkUpdatePricingRatesCommand(
            rates: [
                [
                    'category_id' => 'cat-123',
                    'pricing_class_id' => 'class-123',
                    'duration_id' => 'duration-123',
                    'price' => 50.0,
                ],
            ],
        );

        $this->repository->expects($this->once())
            ->method('findByDimensions')
            ->with('cat-123', 'class-123', 'duration-123')
            ->willReturn($existingRate);

        $this->repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (PricingRate $rate) {
                return $rate->price() === 50.0;
            }));

        $result = $this->handler->handle($command);

        $this->assertEquals(0, $result['created']);
        $this->assertEquals(1, $result['updated']);
    }

    public function test_handles_mixed_create_and_update(): void
    {
        $existingRate = new PricingRate(
            id: 'rate-123',
            categoryId: 'cat-123',
            pricingClassId: 'class-123',
            durationId: 'duration-123',
            price: 30.0,
            isActive: true,
        );

        $command = new BulkUpdatePricingRatesCommand(
            rates: [
                [
                    'category_id' => 'cat-123',
                    'pricing_class_id' => 'class-123',
                    'duration_id' => 'duration-123',
                    'price' => 50.0,
                ],
                [
                    'category_id' => 'cat-456',
                    'pricing_class_id' => 'class-456',
                    'duration_id' => 'duration-456',
                    'price' => 75.0,
                ],
            ],
        );

        $this->repository->expects($this->exactly(2))
            ->method('findByDimensions')
            ->willReturnCallback(function ($catId, $classId, $durId) use ($existingRate) {
                if ($catId === 'cat-123') {
                    return $existingRate;
                }

                return null;
            });

        $this->repository->expects($this->exactly(2))
            ->method('save');

        $result = $this->handler->handle($command);

        $this->assertEquals(1, $result['created']);
        $this->assertEquals(1, $result['updated']);
    }

    public function test_deactivates_rate_when_is_active_is_false(): void
    {
        $existingRate = new PricingRate(
            id: 'rate-123',
            categoryId: 'cat-123',
            pricingClassId: 'class-123',
            durationId: 'duration-123',
            price: 30.0,
            isActive: true,
        );

        $command = new BulkUpdatePricingRatesCommand(
            rates: [
                [
                    'category_id' => 'cat-123',
                    'pricing_class_id' => 'class-123',
                    'duration_id' => 'duration-123',
                    'price' => 50.0,
                    'is_active' => false,
                ],
            ],
        );

        $this->repository->expects($this->once())
            ->method('findByDimensions')
            ->willReturn($existingRate);

        $this->repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (PricingRate $rate) {
                return $rate->isActive() === false;
            }));

        $result = $this->handler->handle($command);

        $this->assertEquals(0, $result['created']);
        $this->assertEquals(1, $result['updated']);
    }

    public function test_activates_rate_when_is_active_is_true(): void
    {
        $existingRate = new PricingRate(
            id: 'rate-123',
            categoryId: 'cat-123',
            pricingClassId: 'class-123',
            durationId: 'duration-123',
            price: 30.0,
            isActive: false,
        );

        $command = new BulkUpdatePricingRatesCommand(
            rates: [
                [
                    'category_id' => 'cat-123',
                    'pricing_class_id' => 'class-123',
                    'duration_id' => 'duration-123',
                    'price' => 50.0,
                    'is_active' => true,
                ],
            ],
        );

        $this->repository->expects($this->once())
            ->method('findByDimensions')
            ->willReturn($existingRate);

        $this->repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (PricingRate $rate) {
                return $rate->isActive() === true;
            }));

        $result = $this->handler->handle($command);

        $this->assertEquals(0, $result['created']);
        $this->assertEquals(1, $result['updated']);
    }

    public function test_creates_inactive_rate_when_is_active_is_false(): void
    {
        $command = new BulkUpdatePricingRatesCommand(
            rates: [
                [
                    'category_id' => 'cat-123',
                    'pricing_class_id' => 'class-123',
                    'duration_id' => 'duration-123',
                    'price' => 50.0,
                    'is_active' => false,
                ],
            ],
        );

        $this->repository->expects($this->once())
            ->method('findByDimensions')
            ->willReturn(null);

        $this->repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (PricingRate $rate) {
                return $rate->isActive() === false;
            }));

        $result = $this->handler->handle($command);

        $this->assertEquals(1, $result['created']);
        $this->assertEquals(0, $result['updated']);
    }
}
