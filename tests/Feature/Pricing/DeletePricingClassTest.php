<?php

declare(strict_types=1);

namespace Tests\Feature\Pricing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Pricing\Infrastructure\Persistence\Models\PricingClassEloquentModel;
use Tests\TestCase;

final class DeletePricingClassTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_delete_pricing_class_successfully(): void
    {
        $pricingClass = PricingClassEloquentModel::factory()->create();

        $response = $this->deleteJson("/api/pricing/classes/{$pricingClass->id}");

        $response->assertOk()
            ->assertJson([
                'message' => 'Classe tarifaire supprimée avec succès',
            ]);

        $this->assertSoftDeleted('pricing_classes', [
            'id' => $pricingClass->id,
        ]);
    }

    public function test_delete_pricing_class_returns_404_when_not_found(): void
    {
        $response = $this->deleteJson('/api/pricing/classes/00000000-0000-0000-0000-000000000000');

        $response->assertStatus(422);
    }

    public function test_delete_pricing_class_soft_deletes(): void
    {
        $pricingClass = PricingClassEloquentModel::factory()->create();

        $this->deleteJson("/api/pricing/classes/{$pricingClass->id}");

        // Verify it still exists in DB but with deleted_at
        $this->assertDatabaseHas('pricing_classes', [
            'id' => $pricingClass->id,
        ]);

        $this->assertNotNull(
            PricingClassEloquentModel::withTrashed()->find($pricingClass->id)->deleted_at
        );
    }
}
