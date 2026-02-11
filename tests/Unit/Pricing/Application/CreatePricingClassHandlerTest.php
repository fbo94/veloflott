<?php

declare(strict_types=1);

namespace Tests\Unit\Pricing\Application;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Pricing\Application\CreatePricingClass\CreatePricingClassCommand;
use Pricing\Application\CreatePricingClass\CreatePricingClassHandler;
use Pricing\Domain\PricingClass;
use Pricing\Domain\PricingClassRepositoryInterface;

final class CreatePricingClassHandlerTest extends TestCase
{
    /** @var MockObject&PricingClassRepositoryInterface */
    private PricingClassRepositoryInterface $repository;
    private CreatePricingClassHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(PricingClassRepositoryInterface::class);
        $this->handler = new CreatePricingClassHandler($this->repository);
    }

    public function test_create_pricing_class_successfully(): void
    {
        $command = new CreatePricingClassCommand(
            code: 'premium',
            label: 'Premium',
            description: 'Premium bikes',
            color: '#FFD700',
            sortOrder: 2,
        );

        $this->repository
            ->expects($this->once())
            ->method('existsWithCode')
            ->with('premium')
            ->willReturn(false);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (PricingClass $pricingClass) {
                return $pricingClass->code() === 'premium'
                    && $pricingClass->label() === 'Premium'
                    && $pricingClass->description() === 'Premium bikes'
                    && $pricingClass->color() === '#FFD700'
                    && $pricingClass->sortOrder() === 2
                    && $pricingClass->isActive() === true;
            }));

        $result = $this->handler->handle($command);

        $this->assertEquals('premium', $result->code);
        $this->assertEquals('Premium', $result->label);
        $this->assertEquals('Premium bikes', $result->description);
        $this->assertEquals('#FFD700', $result->color);
        $this->assertEquals(2, $result->sortOrder);
        $this->assertTrue($result->isActive);
    }

    public function test_throws_exception_when_code_already_exists(): void
    {
        $command = new CreatePricingClassCommand(
            code: 'standard',
            label: 'Standard',
            description: null,
            color: null,
            sortOrder: 1,
        );

        $this->repository
            ->expects($this->once())
            ->method('existsWithCode')
            ->with('standard')
            ->willReturn(true);

        $this->repository
            ->expects($this->never())
            ->method('save');

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage("Pricing class with code 'standard' already exists");

        $this->handler->handle($command);
    }

    public function test_create_pricing_class_with_minimal_data(): void
    {
        $command = new CreatePricingClassCommand(
            code: 'basic',
            label: 'Basic',
            description: null,
            color: null,
            sortOrder: 1,
        );

        $this->repository
            ->method('existsWithCode')
            ->willReturn(false);

        $this->repository
            ->expects($this->once())
            ->method('save');

        $result = $this->handler->handle($command);

        $this->assertEquals('basic', $result->code);
        $this->assertEquals('Basic', $result->label);
        $this->assertNull($result->description);
        $this->assertNull($result->color);
        $this->assertEquals(1, $result->sortOrder);
        $this->assertTrue($result->isActive);
    }
}
