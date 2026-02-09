<?php

declare(strict_types=1);

namespace Tests\Feature\Pricing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Pricing\Infrastructure\Persistence\Models\DurationDefinitionEloquentModel;
use Tests\TestCase;

final class UpdateDurationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_update_duration_successfully(): void
    {
        $duration = DurationDefinitionEloquentModel::factory()->create([
            'code' => 'week',
            'label' => 'Semaine',
            'duration_days' => 7,
        ]);

        $data = [
            'code' => 'week',
            'label' => 'Semaine (7 jours)',
            'duration_days' => 7,
            'sort_order' => 3,
        ];

        $response = $this->putJson("/api/pricing/durations/{$duration->id}", $data);

        $response->assertOk()
            ->assertJsonPath('label', 'Semaine (7 jours)');
    }

    public function test_update_duration_can_deactivate(): void
    {
        $duration = DurationDefinitionEloquentModel::factory()->create([
            'code' => 'month',
            'is_active' => true,
        ]);

        $data = [
            'code' => 'month',
            'label' => 'Mois',
            'duration_days' => 30,
            'is_active' => false,
        ];

        $response = $this->putJson("/api/pricing/durations/{$duration->id}", $data);

        $response->assertOk()
            ->assertJsonPath('is_active', false);
    }

    public function test_update_duration_validates_code_uniqueness(): void
    {
        $existing = DurationDefinitionEloquentModel::factory()->create(['code' => 'existing']);
        $toUpdate = DurationDefinitionEloquentModel::factory()->create(['code' => 'to_update']);

        $data = [
            'code' => 'existing',
            'label' => 'Test',
            'duration_days' => 1,
        ];

        $response = $this->putJson("/api/pricing/durations/{$toUpdate->id}", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_update_duration_returns_404_when_not_found(): void
    {
        $data = [
            'code' => 'test',
            'label' => 'Test',
            'duration_days' => 1,
        ];

        $response = $this->putJson('/api/pricing/durations/00000000-0000-0000-0000-000000000000', $data);

        $response->assertStatus(422);
    }
}
