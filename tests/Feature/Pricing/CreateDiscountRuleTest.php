<?php

declare(strict_types=1);

namespace Tests\Feature\Pricing;

use Fleet\Infrastructure\Persistence\Models\CategoryEloquentModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Pricing\Infrastructure\Persistence\Models\DurationDefinitionEloquentModel;
use Pricing\Infrastructure\Persistence\Models\PricingClassEloquentModel;
use Tests\TestCase;

final class CreateDiscountRuleTest extends TestCase
{
    use RefreshDatabase;

    private string $categoryId;

    private string $pricingClassId;

    private string $durationId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();

        $this->categoryId = '550e8400-e29b-41d4-a716-446655440000';
        CategoryEloquentModel::create([
            'id' => $this->categoryId,
            'name' => 'Test Category',
            'code' => 'test',
            'description' => 'Test',
            'sort_order' => 1,
        ]);

        $pricingClass = PricingClassEloquentModel::factory()->create();
        $this->pricingClassId = $pricingClass->id;

        $duration = DurationDefinitionEloquentModel::factory()->create();
        $this->durationId = $duration->id;
    }

    public function test_create_percentage_discount(): void
    {
        $data = [
            'category_id' => $this->categoryId,
            'discount_type' => 'percentage',
            'discount_value' => 10.0,
            'label' => 'Réduction 10%',
            'min_days' => 7,
        ];

        $response = $this->postJson('/api/pricing/discounts', $data);

        $response->assertCreated()
            ->assertJsonPath('discount_type', 'percentage')
            ->assertJsonPath('discount_value', 10)
            ->assertJsonPath('label', 'Réduction 10%');

        $this->assertDatabaseHas('discount_rules', [
            'category_id' => $this->categoryId,
            'discount_type' => 'percentage',
            'discount_value' => 10.0,
        ]);
    }

    public function test_create_fixed_discount(): void
    {
        $data = [
            'pricing_class_id' => $this->pricingClassId,
            'discount_type' => 'fixed',
            'discount_value' => 50.0,
            'label' => 'Réduction 50€',
            'min_duration_id' => $this->durationId,
        ];

        $response = $this->postJson('/api/pricing/discounts', $data);

        $response->assertCreated()
            ->assertJsonPath('discount_type', 'fixed');
    }

    public function test_create_cumulative_discount(): void
    {
        $data = [
            'discount_type' => 'percentage',
            'discount_value' => 5.0,
            'label' => 'Réduction cumulable',
            'is_cumulative' => true,
            'priority' => 10,
            'min_days' => 1,
        ];

        $response = $this->postJson('/api/pricing/discounts', $data);

        $response->assertCreated()
            ->assertJsonPath('is_cumulative', true)
            ->assertJsonPath('priority', 10);
    }

    public function test_create_discount_validates_required_fields(): void
    {
        $data = [];

        $response = $this->postJson('/api/pricing/discounts', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['discount_type', 'discount_value', 'label']);
    }

    public function test_create_discount_validates_discount_type(): void
    {
        $data = [
            'discount_type' => 'invalid',
            'discount_value' => 10.0,
            'label' => 'Test',
            'min_days' => 1,
        ];

        $response = $this->postJson('/api/pricing/discounts', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['discount_type']);
    }

    public function test_create_discount_validates_foreign_keys(): void
    {
        $data = [
            'category_id' => '00000000-0000-0000-0000-000000000000',
            'discount_type' => 'percentage',
            'discount_value' => 10.0,
            'label' => 'Test',
            'min_days' => 1,
        ];

        $response = $this->postJson('/api/pricing/discounts', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category_id']);
    }
}
