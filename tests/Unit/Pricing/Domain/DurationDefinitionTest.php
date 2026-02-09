<?php

declare(strict_types=1);

namespace Tests\Unit\Pricing\Domain;

use PHPUnit\Framework\TestCase;
use Pricing\Domain\DurationDefinition;

final class DurationDefinitionTest extends TestCase
{
    public function test_create_duration_with_days(): void
    {
        $duration = DurationDefinition::create(
            id: 'duration-123',
            code: 'week',
            label: 'Semaine',
            durationDays: 7,
        );

        $this->assertEquals('duration-123', $duration->id());
        $this->assertEquals('week', $duration->code());
        $this->assertEquals('Semaine', $duration->label());
        $this->assertNull($duration->durationHours());
        $this->assertEquals(7, $duration->durationDays());
        $this->assertFalse($duration->isCustom());
        $this->assertTrue($duration->isActive());
    }

    public function test_create_duration_with_hours(): void
    {
        $duration = DurationDefinition::create(
            id: 'duration-123',
            code: 'half_day',
            label: 'Demi-journée',
            durationHours: 4,
        );

        $this->assertEquals(4, $duration->durationHours());
        $this->assertNull($duration->durationDays());
    }

    public function test_create_custom_duration(): void
    {
        $duration = DurationDefinition::create(
            id: 'duration-123',
            code: 'custom',
            label: 'Durée personnalisée',
            isCustom: true,
        );

        $this->assertTrue($duration->isCustom());
        $this->assertNull($duration->durationHours());
        $this->assertNull($duration->durationDays());
    }

    public function test_create_duration_with_both_hours_and_days(): void
    {
        $duration = DurationDefinition::create(
            id: 'duration-123',
            code: 'full_day',
            label: 'Journée',
            durationHours: 8,
            durationDays: 1,
        );

        $this->assertEquals(8, $duration->durationHours());
        $this->assertEquals(1, $duration->durationDays());
    }

    public function test_total_hours_calculates_from_days(): void
    {
        $duration = DurationDefinition::create(
            id: 'duration-123',
            code: 'week',
            label: 'Semaine',
            durationDays: 7,
        );

        $this->assertEquals(168, $duration->totalHours()); // 7 * 24
    }

    public function test_total_hours_returns_hours_when_no_days(): void
    {
        $duration = DurationDefinition::create(
            id: 'duration-123',
            code: 'half_day',
            label: 'Demi-journée',
            durationHours: 4,
        );

        $this->assertEquals(4, $duration->totalHours());
    }

    public function test_total_hours_returns_null_for_custom_duration(): void
    {
        $duration = DurationDefinition::create(
            id: 'duration-123',
            code: 'custom',
            label: 'Personnalisé',
            isCustom: true,
        );

        $this->assertNull($duration->totalHours());
    }

    public function test_approximate_days_returns_days(): void
    {
        $duration = DurationDefinition::create(
            id: 'duration-123',
            code: 'week',
            label: 'Semaine',
            durationDays: 7,
        );

        $this->assertEquals(7.0, $duration->approximateDays());
    }

    public function test_approximate_days_calculates_from_hours(): void
    {
        $duration = DurationDefinition::create(
            id: 'duration-123',
            code: 'half_day',
            label: 'Demi-journée',
            durationHours: 12,
        );

        $this->assertEquals(0.5, $duration->approximateDays()); // 12 / 24
    }

    public function test_create_duration_throws_exception_when_code_empty(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Duration code cannot be empty');

        DurationDefinition::create(
            id: 'duration-123',
            code: '',
            label: 'Test',
            durationDays: 1,
        );
    }

    public function test_create_duration_throws_exception_when_code_invalid(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Duration code must contain only lowercase letters, numbers and underscores');

        DurationDefinition::create(
            id: 'duration-123',
            code: 'Invalid-Code',
            label: 'Test',
            durationDays: 1,
        );
    }

    public function test_create_duration_throws_exception_when_label_empty(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Duration label cannot be empty');

        DurationDefinition::create(
            id: 'duration-123',
            code: 'test',
            label: '',
            durationDays: 1,
        );
    }

    public function test_create_duration_throws_exception_when_no_duration_specified(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Duration must have either hours or days specified');

        DurationDefinition::create(
            id: 'duration-123',
            code: 'test',
            label: 'Test',
        );
    }

    public function test_create_duration_throws_exception_when_hours_less_than_one(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Duration hours must be at least 1');

        DurationDefinition::create(
            id: 'duration-123',
            code: 'test',
            label: 'Test',
            durationHours: 0,
        );
    }

    public function test_create_duration_throws_exception_when_days_less_than_one(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Duration days must be at least 1');

        DurationDefinition::create(
            id: 'duration-123',
            code: 'test',
            label: 'Test',
            durationDays: 0,
        );
    }

    public function test_update_duration(): void
    {
        $duration = DurationDefinition::create(
            id: 'duration-123',
            code: 'week',
            label: 'Semaine',
            durationDays: 7,
        );

        $duration->update(
            label: 'Semaine complète',
            durationHours: null,
            durationDays: 7,
            sortOrder: 5,
        );

        $this->assertEquals('Semaine complète', $duration->label());
        $this->assertEquals(5, $duration->sortOrder());
    }

    public function test_activate_duration(): void
    {
        $duration = DurationDefinition::create(
            id: 'duration-123',
            code: 'week',
            label: 'Semaine',
            durationDays: 7,
        );

        $duration->deactivate();
        $this->assertFalse($duration->isActive());

        $duration->activate();
        $this->assertTrue($duration->isActive());
    }

    public function test_activate_throws_exception_when_already_active(): void
    {
        $duration = DurationDefinition::create(
            id: 'duration-123',
            code: 'week',
            label: 'Semaine',
            durationDays: 7,
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Duration is already active');

        $duration->activate();
    }

    public function test_deactivate_duration(): void
    {
        $duration = DurationDefinition::create(
            id: 'duration-123',
            code: 'week',
            label: 'Semaine',
            durationDays: 7,
        );

        $duration->deactivate();
        $this->assertFalse($duration->isActive());
    }

    public function test_deactivate_throws_exception_when_already_inactive(): void
    {
        $duration = DurationDefinition::create(
            id: 'duration-123',
            code: 'week',
            label: 'Semaine',
            durationDays: 7,
        );

        $duration->deactivate();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Duration is already inactive');

        $duration->deactivate();
    }
}
