<?php

declare(strict_types=1);

namespace Tests\Unit\Pricing\Application;

use PHPUnit\Framework\TestCase;
use Pricing\Application\UpdatePricingClass\UpdatePricingClassCommand;
use Pricing\Application\UpdatePricingClass\UpdatePricingClassHandler;
use Pricing\Domain\PricingClass;
use Pricing\Domain\PricingClassRepositoryInterface;

final class UpdatePricingClassHandlerTest extends TestCase
{
    private PricingClassRepositoryInterface $repository;
    private UpdatePricingClassHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(PricingClassRepositoryInterface::class);
        $this->handler = new UpdatePricingClassHandler($this->repository);
    }

    public function test_update_pricing_class_successfully(): void
    {
        $pricingClass = PricingClass::create(
            id: 'class-123',
            code: 'standard',
            label: 'Standard',
            description: 'Old description',
            color: '#000000',
            sortOrder: 1,
        );

        $command = new UpdatePricingClassCommand(
            id: 'class-123',
            code: 'standard',
            label: 'Standard Updated',
            description: 'New description',
            color: '#FFFFFF',
            sortOrder: 2,
            isActive: true,
        );

        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with('class-123')
            ->willReturn($pricingClass);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($pricingClass);

        $result = $this->handler->handle($command);

        $this->assertEquals('class-123', $result->id);
        $this->assertEquals('Standard Updated', $result->label);
        $this->assertEquals('New description', $result->description);
        $this->assertEquals('#FFFFFF', $result->color);
        $this->assertEquals(2, $result->sortOrder);
        $this->assertTrue($result->isActive);
    }

    public function test_throws_exception_when_pricing_class_not_found(): void
    {
        $command = new UpdatePricingClassCommand(
            id: 'non-existent',
            code: 'test',
            label: 'Test',
            description: null,
            color: null,
            sortOrder: 1,
            isActive: true,
        );

        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with('non-existent')
            ->willReturn(null);

        $this->repository
            ->expects($this->never())
            ->method('save');

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('PricingClass with id non-existent not found');

        $this->handler->handle($command);
    }

    public function test_activate_pricing_class(): void
    {
        $pricingClass = PricingClass::create(
            id: 'class-123',
            code: 'standard',
            label: 'Standard',
            description: null,
            color: null,
            sortOrder: 1,
        );
        $pricingClass->deactivate();

        $command = new UpdatePricingClassCommand(
            id: 'class-123',
            code: 'standard',
            label: 'Standard',
            description: null,
            color: null,
            sortOrder: 1,
            isActive: true,
        );

        $this->repository
            ->method('findById')
            ->willReturn($pricingClass);

        $result = $this->handler->handle($command);

        $this->assertTrue($result->isActive);
    }

    public function test_deactivate_pricing_class(): void
    {
        $pricingClass = PricingClass::create(
            id: 'class-123',
            code: 'standard',
            label: 'Standard',
            description: null,
            color: null,
            sortOrder: 1,
        );

        $command = new UpdatePricingClassCommand(
            id: 'class-123',
            code: 'standard',
            label: 'Standard',
            description: null,
            color: null,
            sortOrder: 1,
            isActive: false,
        );

        $this->repository
            ->method('findById')
            ->willReturn($pricingClass);

        $result = $this->handler->handle($command);

        $this->assertFalse($result->isActive);
    }
}
