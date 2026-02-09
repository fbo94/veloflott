<?php

declare(strict_types=1);

namespace Tests\Feature\Pricing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Pricing\Infrastructure\Persistence\Models\DiscountRuleEloquentModel;
use Tests\TestCase;

final class DeleteDiscountRuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_delete_discount_rule_successfully(): void
    {
        $discount = DiscountRuleEloquentModel::factory()->create();

        $response = $this->deleteJson("/api/pricing/discounts/{$discount->id}");

        $response->assertOk()
            ->assertJson([
                'message' => 'Règle de réduction supprimée avec succès',
            ]);

        $this->assertSoftDeleted('discount_rules', [
            'id' => $discount->id,
        ]);
    }

    public function test_delete_discount_rule_returns_404_when_not_found(): void
    {
        $response = $this->deleteJson('/api/pricing/discounts/00000000-0000-0000-0000-000000000000');

        $response->assertStatus(422);
    }

    public function test_delete_discount_rule_soft_deletes(): void
    {
        $discount = DiscountRuleEloquentModel::factory()->create();

        $this->deleteJson("/api/pricing/discounts/{$discount->id}");

        $this->assertDatabaseHas('discount_rules', [
            'id' => $discount->id,
        ]);

        $this->assertNotNull(
            DiscountRuleEloquentModel::withTrashed()->find($discount->id)->deleted_at
        );
    }
}
