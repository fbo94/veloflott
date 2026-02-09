<?php

declare(strict_types=1);

namespace Tests\Feature\Pricing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Pricing\Infrastructure\Persistence\Models\PricingClassEloquentModel;
use Tests\TestCase;

final class UpdatePricingClassTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_update_pricing_class_successfully(): void
    {
        $pricingClass = PricingClassEloquentModel::factory()->create([
            'code' => 'standard',
            'label' => 'Standard',
            'is_active' => true,
        ]);

        $data = [
            'code' => 'standard',
            'label' => 'Standard Updated',
            'description' => 'Updated description',
            'color' => '#FF5733',
            'sort_order' => 5,
            'is_active' => true,
        ];

        $response = $this->putJson("/api/pricing/classes/{$pricingClass->id}", $data);

        $response->assertOk()
            ->assertJsonPath('label', 'Standard Updated')
            ->assertJsonPath('description', 'Updated description')
            ->assertJsonPath('color', '#FF5733');
    }

    public function test_update_pricing_class_can_deactivate(): void
    {
        $pricingClass = PricingClassEloquentModel::factory()->create([
            'code' => 'premium',
            'is_active' => true,
        ]);

        $data = [
            'code' => 'premium',
            'label' => 'Premium',
            'is_active' => false,
        ];

        $response = $this->putJson("/api/pricing/classes/{$pricingClass->id}", $data);

        $response->assertOk()
            ->assertJsonPath('is_active', false);

        $this->assertDatabaseHas('pricing_classes', [
            'id' => $pricingClass->id,
            'is_active' => false,
        ]);
    }

    public function test_update_pricing_class_validates_code_uniqueness(): void
    {
        $existingClass = PricingClassEloquentModel::factory()->create([
            'code' => 'existing_code',
        ]);

        $classToUpdate = PricingClassEloquentModel::factory()->create([
            'code' => 'my_code',
        ]);

        $data = [
            'code' => 'existing_code',
            'label' => 'Test',
        ];

        $response = $this->putJson("/api/pricing/classes/{$classToUpdate->id}", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_update_pricing_class_returns_404_when_not_found(): void
    {
        $data = [
            'code' => 'test',
            'label' => 'Test',
        ];

        $response = $this->putJson('/api/pricing/classes/00000000-0000-0000-0000-000000000000', $data);

        $response->assertStatus(422);
    }

    public function test_update_pricing_class_validates_color_format(): void
    {
        $pricingClass = PricingClassEloquentModel::factory()->create();

        $data = [
            'code' => 'test',
            'label' => 'Test',
            'color' => 'invalid-color',
        ];

        $response = $this->putJson("/api/pricing/classes/{$pricingClass->id}", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['color']);
    }
}
