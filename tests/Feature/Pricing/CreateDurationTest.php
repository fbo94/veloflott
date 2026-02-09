<?php

declare(strict_types=1);

namespace Tests\Feature\Pricing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CreateDurationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_create_duration_with_days(): void
    {
        $data = [
            'code' => 'full_day',
            'label' => 'Journée complète',
            'duration_days' => 1,
            'sort_order' => 1,
        ];

        $response = $this->postJson('/api/pricing/durations', $data);

        $response->assertCreated()
            ->assertJsonPath('code', 'full_day')
            ->assertJsonPath('label', 'Journée complète')
            ->assertJsonPath('duration_days', 1)
            ->assertJsonPath('is_active', true);

        $this->assertDatabaseHas('duration_definitions', [
            'code' => 'full_day',
            'duration_days' => 1,
        ]);
    }

    public function test_create_duration_with_hours(): void
    {
        $data = [
            'code' => 'half_day',
            'label' => 'Demi-journée',
            'duration_hours' => 4,
        ];

        $response = $this->postJson('/api/pricing/durations', $data);

        $response->assertCreated()
            ->assertJsonPath('duration_hours', 4);
    }

    public function test_create_custom_duration(): void
    {
        $data = [
            'code' => 'custom',
            'label' => 'Durée personnalisée',
            'is_custom' => true,
        ];

        $response = $this->postJson('/api/pricing/durations', $data);

        $response->assertCreated()
            ->assertJsonPath('is_custom', true);
    }

    public function test_create_duration_validates_code_uniqueness(): void
    {
        $data = [
            'code' => 'week',
            'label' => 'Semaine',
            'duration_days' => 7,
        ];

        $this->postJson('/api/pricing/durations', $data);

        // Try to create with same code
        $response = $this->postJson('/api/pricing/durations', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_create_duration_validates_required_fields(): void
    {
        $data = [];

        $response = $this->postJson('/api/pricing/durations', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'label']);
    }

    public function test_create_duration_validates_code_format(): void
    {
        $data = [
            'code' => 'Invalid-Code',
            'label' => 'Test',
            'duration_days' => 1,
        ];

        $response = $this->postJson('/api/pricing/durations', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }
}
