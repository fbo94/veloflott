<?php

declare(strict_types=1);

namespace Tests\Feature\Pricing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Pricing\Infrastructure\Persistence\Models\DurationDefinitionEloquentModel;
use Tests\TestCase;

final class ListDurationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_list_durations_returns_only_active_durations(): void
    {
        DurationDefinitionEloquentModel::factory()->create([
            'code' => 'full_day',
            'label' => 'Journée',
            'is_active' => true,
        ]);

        DurationDefinitionEloquentModel::factory()->create([
            'code' => 'week',
            'label' => 'Semaine',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/pricing/durations');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'full_day');
    }

    public function test_list_durations_returns_sorted_by_sort_order(): void
    {
        DurationDefinitionEloquentModel::factory()->create([
            'code' => 'month',
            'label' => 'Mois',
            'sort_order' => 7,
            'is_active' => true,
        ]);

        DurationDefinitionEloquentModel::factory()->create([
            'code' => 'half_day',
            'label' => 'Demi-journée',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        DurationDefinitionEloquentModel::factory()->create([
            'code' => 'week',
            'label' => 'Semaine',
            'sort_order' => 5,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/pricing/durations');

        $response->assertOk()
            ->assertJsonPath('data.0.code', 'half_day')
            ->assertJsonPath('data.1.code', 'week')
            ->assertJsonPath('data.2.code', 'month');
    }

    public function test_list_durations_includes_all_expected_fields(): void
    {
        DurationDefinitionEloquentModel::factory()->create([
            'code' => 'full_day',
            'label' => 'Journée complète',
            'duration_hours' => 8,
            'duration_days' => 1,
            'is_custom' => false,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/pricing/durations');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'code',
                        'label',
                        'duration_hours',
                        'duration_days',
                        'is_custom',
                        'sort_order',
                        'is_active',
                    ],
                ],
            ]);
    }

    public function test_list_durations_returns_empty_array_when_no_active_durations(): void
    {
        $response = $this->getJson('/api/pricing/durations');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
