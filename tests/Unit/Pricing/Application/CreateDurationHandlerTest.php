<?php

declare(strict_types=1);

namespace Tests\Unit\Pricing\Application;

use PHPUnit\Framework\TestCase;
use Pricing\Application\CreateDuration\CreateDurationCommand;
use Pricing\Application\CreateDuration\CreateDurationHandler;
use Pricing\Domain\DurationDefinition;
use Pricing\Domain\DurationDefinitionRepositoryInterface;

final class CreateDurationHandlerTest extends TestCase
{
    private DurationDefinitionRepositoryInterface $repository;
    private CreateDurationHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(DurationDefinitionRepositoryInterface::class);
        $this->handler = new CreateDurationHandler($this->repository);
    }

    public function test_create_duration_with_days(): void
    {
        $command = new CreateDurationCommand(
            code: 'week',
            label: 'Semaine',
            durationHours: null,
            durationDays: 7,
            isCustom: false,
            sortOrder: 5,
        );

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (DurationDefinition $duration) {
                return $duration->code() === 'week'
                    && $duration->label() === 'Semaine'
                    && $duration->durationHours() === null
                    && $duration->durationDays() === 7
                    && $duration->isCustom() === false
                    && $duration->sortOrder() === 5
                    && $duration->isActive() === true;
            }));

        $result = $this->handler->handle($command);

        $this->assertEquals('week', $result->code);
        $this->assertEquals('Semaine', $result->label);
        $this->assertNull($result->durationHours);
        $this->assertEquals(7, $result->durationDays);
        $this->assertFalse($result->isCustom);
        $this->assertEquals(5, $result->sortOrder);
        $this->assertTrue($result->isActive);
    }

    public function test_create_duration_with_hours(): void
    {
        $command = new CreateDurationCommand(
            code: 'half_day',
            label: 'Demi-journée',
            durationHours: 4,
            durationDays: null,
            isCustom: false,
            sortOrder: 1,
        );

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (DurationDefinition $duration) {
                return $duration->code() === 'half_day'
                    && $duration->label() === 'Demi-journée'
                    && $duration->durationHours() === 4
                    && $duration->durationDays() === null;
            }));

        $result = $this->handler->handle($command);

        $this->assertEquals('half_day', $result->code);
        $this->assertEquals('Demi-journée', $result->label);
        $this->assertEquals(4, $result->durationHours);
        $this->assertNull($result->durationDays);
    }

    public function test_create_custom_duration(): void
    {
        $command = new CreateDurationCommand(
            code: 'custom',
            label: 'Durée personnalisée',
            durationHours: null,
            durationDays: null,
            isCustom: true,
            sortOrder: 99,
        );

        $this->repository
            ->expects($this->once())
            ->method('save');

        $result = $this->handler->handle($command);

        $this->assertEquals('custom', $result->code);
        $this->assertTrue($result->isCustom);
        $this->assertNull($result->durationHours);
        $this->assertNull($result->durationDays);
    }

    public function test_create_duration_with_both_hours_and_days(): void
    {
        $command = new CreateDurationCommand(
            code: 'full_day',
            label: 'Journée complète',
            durationHours: 8,
            durationDays: 1,
            isCustom: false,
            sortOrder: 2,
        );

        $this->repository
            ->expects($this->once())
            ->method('save');

        $result = $this->handler->handle($command);

        $this->assertEquals('full_day', $result->code);
        $this->assertEquals(8, $result->durationHours);
        $this->assertEquals(1, $result->durationDays);
    }
}
