<?php

declare(strict_types=1);

namespace Tests\Feature\Pricing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Pricing\Infrastructure\Persistence\Models\DiscountRuleEloquentModel;
use Tests\TestCase;

final class UpdateDiscountRuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_update_discount_rule_successfully(): void
    {
        $discount = DiscountRuleEloquentModel::factory()->create([
            'discount_type' => 'percentage',
            'discount_value' => 10.0,
            'label' => 'Réduction 10%',
        ]);

        $data = [
            'discount_type' => 'percentage',
            'discount_value' => 15.0,
            'label' => 'Réduction 15%',
            'description' => 'Updated description',
            'min_days' => 7,
        ];

        $response = $this->putJson("/api/pricing/discounts/{$discount->id}", $data);

        $response->assertOk()
            ->assertJsonPath('discount_value', 15)
            ->assertJsonPath('label', 'Réduction 15%');
    }

    public function test_update_discount_rule_can_deactivate(): void
    {
        $discount = DiscountRuleEloquentModel::factory()->create([
            'is_active' => true,
        ]);

        $data = [
            'discount_type' => 'percentage',
            'discount_value' => 10.0,
            'label' => 'Test',
            'is_active' => false,
            'min_days' => 1,
        ];

        $response = $this->putJson("/api/pricing/discounts/{$discount->id}", $data);

        $response->assertOk()
            ->assertJsonPath('is_active', false);
    }

    public function test_update_discount_rule_returns_404_when_not_found(): void
    {
        $data = [
            'discount_type' => 'percentage',
            'discount_value' => 10.0,
            'label' => 'Test',
            'min_days' => 1,
        ];

        $response = $this->putJson('/api/pricing/discounts/00000000-0000-0000-0000-000000000000', $data);

        $response->assertStatus(422);
    }
}
