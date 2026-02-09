<?php

declare(strict_types=1);

namespace Tests\Feature\Pricing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Pricing\Infrastructure\Persistence\Models\PricingClassEloquentModel;
use Tests\TestCase;

final class CreatePricingClassTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_create_pricing_class_successfully(): void
    {
        $data = [
            'code' => 'premium',
            'label' => 'Premium',
            'description' => 'Premium bikes',
            'color' => '#FFD700',
            'sort_order' => 2,
        ];

        $response = $this->postJson('/api/pricing/classes', $data);

        $response->assertCreated()
            ->assertJsonPath('code', 'premium')
            ->assertJsonPath('label', 'Premium')
            ->assertJsonPath('description', 'Premium bikes')
            ->assertJsonPath('color', '#FFD700')
            ->assertJsonPath('sort_order', 2)
            ->assertJsonPath('is_active', true);

        $this->assertDatabaseHas('pricing_classes', [
            'code' => 'premium',
            'label' => 'Premium',
            'is_active' => true,
        ]);
    }

    public function test_create_pricing_class_with_minimal_data(): void
    {
        $data = [
            'code' => 'basic',
            'label' => 'Basic',
        ];

        $response = $this->postJson('/api/pricing/classes', $data);

        $response->assertCreated()
            ->assertJsonPath('code', 'basic')
            ->assertJsonPath('label', 'Basic')
            ->assertJsonPath('is_active', true);

        $this->assertDatabaseHas('pricing_classes', [
            'code' => 'basic',
            'label' => 'Basic',
        ]);
    }

    public function test_create_pricing_class_fails_when_code_already_exists(): void
    {
        PricingClassEloquentModel::factory()->create([
            'code' => 'standard',
            'label' => 'Standard',
        ]);

        $data = [
            'code' => 'standard',
            'label' => 'Another Standard',
        ];

        $response = $this->postJson('/api/pricing/classes', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_create_pricing_class_validates_required_fields(): void
    {
        $response = $this->postJson('/api/pricing/classes', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'label']);
    }

    public function test_create_pricing_class_validates_code_format(): void
    {
        $data = [
            'code' => 'Invalid Code!',
            'label' => 'Test',
        ];

        $response = $this->postJson('/api/pricing/classes', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }
}
