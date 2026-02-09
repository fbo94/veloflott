<?php

declare(strict_types=1);

namespace Tests\Feature\Pricing;

use Fleet\Infrastructure\Persistence\Models\CategoryEloquentModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Pricing\Infrastructure\Persistence\Models\DurationDefinitionEloquentModel;
use Pricing\Infrastructure\Persistence\Models\PricingClassEloquentModel;
use Pricing\Infrastructure\Persistence\Models\PricingRateEloquentModel;
use Tests\TestCase;

final class BulkUpdatePricingRatesTest extends TestCase
{
    use RefreshDatabase;

    private string $categoryId;

    private string $pricingClassId;

    private string $durationId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();

        // Create test category
        $this->categoryId = '550e8400-e29b-41d4-a716-446655440000';
        CategoryEloquentModel::create([
            'id' => $this->categoryId,
            'name' => 'Test Category',
            'code' => 'test',
            'description' => 'Test category',
            'sort_order' => 1,
        ]);

        // Create test pricing class
        $pricingClass = PricingClassEloquentModel::factory()->create([
            'code' => 'standard',
            'is_active' => true,
        ]);
        $this->pricingClassId = $pricingClass->id;

        // Create test duration
        $duration = DurationDefinitionEloquentModel::factory()->create([
            'code' => 'full_day',
            'duration_days' => 1,
            'is_active' => true,
        ]);
        $this->durationId = $duration->id;
    }

    public function test_bulk_update_creates_new_rates(): void
    {
        $data = [
            'rates' => [
                [
                    'category_id' => $this->categoryId,
                    'pricing_class_id' => $this->pricingClassId,
                    'duration_id' => $this->durationId,
                    'price' => 50.0,
                ],
            ],
        ];

        $response = $this->putJson('/api/pricing/rates', $data);

        $response->assertOk()
            ->assertJson([
                'message' => 'Tarifs mis à jour avec succès',
                'created' => 1,
                'updated' => 0,
                'total' => 1,
            ]);

        $this->assertDatabaseHas('pricing_rates', [
            'category_id' => $this->categoryId,
            'pricing_class_id' => $this->pricingClassId,
            'duration_id' => $this->durationId,
            'price' => 50.0,
            'is_active' => true,
        ]);
    }

    public function test_bulk_update_updates_existing_rates(): void
    {
        // Create existing rate
        PricingRateEloquentModel::factory()->create([
            'category_id' => $this->categoryId,
            'pricing_class_id' => $this->pricingClassId,
            'duration_id' => $this->durationId,
            'price' => 30.0,
            'is_active' => true,
        ]);

        $data = [
            'rates' => [
                [
                    'category_id' => $this->categoryId,
                    'pricing_class_id' => $this->pricingClassId,
                    'duration_id' => $this->durationId,
                    'price' => 55.0,
                ],
            ],
        ];

        $response = $this->putJson('/api/pricing/rates', $data);

        $response->assertOk()
            ->assertJson([
                'created' => 0,
                'updated' => 1,
                'total' => 1,
            ]);

        $this->assertDatabaseHas('pricing_rates', [
            'category_id' => $this->categoryId,
            'pricing_class_id' => $this->pricingClassId,
            'duration_id' => $this->durationId,
            'price' => 55.0,
        ]);
    }

    public function test_bulk_update_handles_mixed_operations(): void
    {
        // Create second set of test data
        $categoryId2 = '650e8400-e29b-41d4-a716-446655440001';
        CategoryEloquentModel::create([
            'id' => $categoryId2,
            'name' => 'Test Category 2',
            'code' => 'test2',
            'description' => 'Test category 2',
            'sort_order' => 2,
        ]);

        // Create existing rate for first category
        PricingRateEloquentModel::factory()->create([
            'category_id' => $this->categoryId,
            'pricing_class_id' => $this->pricingClassId,
            'duration_id' => $this->durationId,
            'price' => 30.0,
            'is_active' => true,
        ]);

        // Mix of update and create
        $data = [
            'rates' => [
                // Update existing
                [
                    'category_id' => $this->categoryId,
                    'pricing_class_id' => $this->pricingClassId,
                    'duration_id' => $this->durationId,
                    'price' => 45.0,
                ],
                // Create new
                [
                    'category_id' => $categoryId2,
                    'pricing_class_id' => $this->pricingClassId,
                    'duration_id' => $this->durationId,
                    'price' => 60.0,
                ],
            ],
        ];

        $response = $this->putJson('/api/pricing/rates', $data);

        $response->assertOk()
            ->assertJson([
                'created' => 1,
                'updated' => 1,
                'total' => 2,
            ]);
    }

    public function test_bulk_update_can_deactivate_rate(): void
    {
        PricingRateEloquentModel::factory()->create([
            'category_id' => $this->categoryId,
            'pricing_class_id' => $this->pricingClassId,
            'duration_id' => $this->durationId,
            'price' => 30.0,
            'is_active' => true,
        ]);

        $data = [
            'rates' => [
                [
                    'category_id' => $this->categoryId,
                    'pricing_class_id' => $this->pricingClassId,
                    'duration_id' => $this->durationId,
                    'price' => 30.0,
                    'is_active' => false,
                ],
            ],
        ];

        $response = $this->putJson('/api/pricing/rates', $data);

        $response->assertOk();

        $this->assertDatabaseHas('pricing_rates', [
            'category_id' => $this->categoryId,
            'is_active' => false,
        ]);
    }

    public function test_bulk_update_validates_required_fields(): void
    {
        $data = [
            'rates' => [
                [
                    'category_id' => $this->categoryId,
                    // Missing pricing_class_id, duration_id, price
                ],
            ],
        ];

        $response = $this->putJson('/api/pricing/rates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'rates.0.pricing_class_id',
                'rates.0.duration_id',
                'rates.0.price',
            ]);
    }

    public function test_bulk_update_validates_price_minimum(): void
    {
        $data = [
            'rates' => [
                [
                    'category_id' => $this->categoryId,
                    'pricing_class_id' => $this->pricingClassId,
                    'duration_id' => $this->durationId,
                    'price' => 0,
                ],
            ],
        ];

        $response = $this->putJson('/api/pricing/rates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rates.0.price']);
    }

    public function test_bulk_update_validates_uuid_format(): void
    {
        $data = [
            'rates' => [
                [
                    'category_id' => 'invalid-uuid',
                    'pricing_class_id' => $this->pricingClassId,
                    'duration_id' => $this->durationId,
                    'price' => 50.0,
                ],
            ],
        ];

        $response = $this->putJson('/api/pricing/rates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rates.0.category_id']);
    }

    public function test_bulk_update_validates_foreign_keys_exist(): void
    {
        $data = [
            'rates' => [
                [
                    'category_id' => '00000000-0000-0000-0000-000000000000',
                    'pricing_class_id' => $this->pricingClassId,
                    'duration_id' => $this->durationId,
                    'price' => 50.0,
                ],
            ],
        ];

        $response = $this->putJson('/api/pricing/rates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rates.0.category_id']);
    }

    public function test_bulk_update_requires_at_least_one_rate(): void
    {
        $data = [
            'rates' => [],
        ];

        $response = $this->putJson('/api/pricing/rates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rates']);
    }
}
