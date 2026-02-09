<?php

declare(strict_types=1);

namespace Tests\Feature\Pricing;

use Fleet\Infrastructure\Persistence\Models\CategoryEloquentModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Pricing\Infrastructure\Persistence\Models\DiscountRuleEloquentModel;
use Pricing\Infrastructure\Persistence\Models\DurationDefinitionEloquentModel;
use Pricing\Infrastructure\Persistence\Models\PricingClassEloquentModel;
use Pricing\Infrastructure\Persistence\Models\PricingRateEloquentModel;
use Tests\TestCase;

final class CalculatePriceTest extends TestCase
{
    use RefreshDatabase;

    private string $categoryId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();

        // Créer une catégorie pour satisfaire les contraintes FK avec un UUID valide
        $this->categoryId = '550e8400-e29b-41d4-a716-446655440000';
        CategoryEloquentModel::create([
            'id' => $this->categoryId,
            'name' => 'Test Category',
            'code' => 'test',
            'description' => 'Test category for pricing tests',
            'sort_order' => 1,
        ]);
    }

    public function test_calculate_price_successfully(): void
    {
        $pricingClass = PricingClassEloquentModel::factory()->create([
            'code' => 'standard',
            'is_active' => true,
        ]);

        $duration = DurationDefinitionEloquentModel::factory()->create([
            'code' => 'full_day',
            'duration_days' => 1,
            'is_active' => true,
        ]);

        PricingRateEloquentModel::factory()->create([
            'category_id' => '550e8400-e29b-41d4-a716-446655440000',
            'pricing_class_id' => $pricingClass->id,
            'duration_id' => $duration->id,
            'price' => 50.0,
            'is_active' => true,
        ]);

        $data = [
            'category_id' => '550e8400-e29b-41d4-a716-446655440000',
            'pricing_class_id' => $pricingClass->id,
            'duration_id' => $duration->id,
        ];

        $response = $this->postJson('/api/pricing/calculate', $data);

        $response->assertOk()
            ->assertJsonPath('base_price', 50)
            ->assertJsonPath('final_price', 50)
            ->assertJsonPath('days', 1)
            ->assertJsonPath('price_per_day', 50);
    }

    public function test_calculate_price_applies_percentage_discount(): void
    {
        $pricingClass = PricingClassEloquentModel::factory()->create([
            'code' => 'standard',
            'is_active' => true,
        ]);

        $duration = DurationDefinitionEloquentModel::factory()->create([
            'code' => 'week',
            'duration_days' => 7,
            'is_active' => true,
        ]);

        PricingRateEloquentModel::factory()->create([
            'category_id' => '550e8400-e29b-41d4-a716-446655440000',
            'pricing_class_id' => $pricingClass->id,
            'duration_id' => $duration->id,
            'price' => 50.0,
            'is_active' => true,
        ]);

        DiscountRuleEloquentModel::factory()->create([
            'min_days' => 7,
            'discount_type' => 'percentage',
            'discount_value' => 10.0,
            'label' => '10% off for weekly rentals',
            'is_active' => true,
        ]);

        $data = [
            'category_id' => '550e8400-e29b-41d4-a716-446655440000',
            'pricing_class_id' => $pricingClass->id,
            'duration_id' => $duration->id,
        ];

        $response = $this->postJson('/api/pricing/calculate', $data);

        $response->assertOk()
            ->assertJsonPath('base_price', 350)
            ->assertJsonPath('final_price', 315)
            ->assertJsonPath('days', 7);
    }

    public function test_calculate_price_with_custom_days(): void
    {
        $pricingClass = PricingClassEloquentModel::factory()->create([
            'code' => 'standard',
            'is_active' => true,
        ]);

        $duration = DurationDefinitionEloquentModel::factory()->create([
            'code' => 'custom',
            'is_custom' => true,
            'is_active' => true,
        ]);

        PricingRateEloquentModel::factory()->create([
            'category_id' => '550e8400-e29b-41d4-a716-446655440000',
            'pricing_class_id' => $pricingClass->id,
            'duration_id' => $duration->id,
            'price' => 50.0,
            'is_active' => true,
        ]);

        $data = [
            'category_id' => '550e8400-e29b-41d4-a716-446655440000',
            'pricing_class_id' => $pricingClass->id,
            'duration_id' => $duration->id,
            'custom_days' => 5,
        ];

        $response = $this->postJson('/api/pricing/calculate', $data);

        $response->assertOk()
            ->assertJsonPath('base_price', 250)
            ->assertJsonPath('final_price', 250)
            ->assertJsonPath('days', 5);
    }

    public function test_calculate_price_returns_404_when_no_rate_found(): void
    {
        // Créer les dépendances mais sans pricing_rate
        $pricingClass = PricingClassEloquentModel::factory()->create();
        $duration = DurationDefinitionEloquentModel::factory()->create();

        $data = [
            'category_id' => $this->categoryId,
            'pricing_class_id' => $pricingClass->id,
            'duration_id' => $duration->id,
        ];

        $response = $this->postJson('/api/pricing/calculate', $data);

        $response->assertNotFound()
            ->assertJsonPath('error', 'No pricing found');
    }

    public function test_calculate_price_validates_required_fields(): void
    {
        $response = $this->postJson('/api/pricing/calculate', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category_id', 'pricing_class_id', 'duration_id']);
    }
}
