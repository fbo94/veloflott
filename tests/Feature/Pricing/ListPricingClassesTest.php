<?php

declare(strict_types=1);

namespace Tests\Feature\Pricing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Pricing\Infrastructure\Persistence\Models\PricingClassEloquentModel;
use Tests\TestCase;

final class ListPricingClassesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_list_pricing_classes_returns_only_active_classes(): void
    {
        // Create active pricing class
        PricingClassEloquentModel::factory()->create([
            'code' => 'standard',
            'label' => 'Standard',
            'is_active' => true,
        ]);

        // Create inactive pricing class
        PricingClassEloquentModel::factory()->create([
            'code' => 'premium',
            'label' => 'Premium',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/pricing/classes');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'standard');
    }

    public function test_list_pricing_classes_returns_sorted_by_sort_order(): void
    {
        PricingClassEloquentModel::factory()->create([
            'code' => 'elite',
            'label' => 'Elite',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        PricingClassEloquentModel::factory()->create([
            'code' => 'standard',
            'label' => 'Standard',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        PricingClassEloquentModel::factory()->create([
            'code' => 'premium',
            'label' => 'Premium',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/pricing/classes');

        $response->assertOk()
            ->assertJsonPath('data.0.code', 'standard')
            ->assertJsonPath('data.1.code', 'premium')
            ->assertJsonPath('data.2.code', 'elite');
    }

    public function test_list_pricing_classes_returns_empty_array_when_no_active_classes(): void
    {
        $response = $this->getJson('/api/pricing/classes');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_list_pricing_classes_includes_all_expected_fields(): void
    {
        PricingClassEloquentModel::factory()->create([
            'code' => 'standard',
            'label' => 'Standard',
            'description' => 'Standard bikes',
            'color' => '#0000FF',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/pricing/classes');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'code',
                        'label',
                        'description',
                        'color',
                        'sort_order',
                        'is_active',
                    ],
                ],
            ]);
    }
}
