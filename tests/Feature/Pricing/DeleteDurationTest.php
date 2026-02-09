<?php

declare(strict_types=1);

namespace Tests\Feature\Pricing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Pricing\Infrastructure\Persistence\Models\DurationDefinitionEloquentModel;
use Tests\TestCase;

final class DeleteDurationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_delete_duration_successfully(): void
    {
        $duration = DurationDefinitionEloquentModel::factory()->create();

        $response = $this->deleteJson("/api/pricing/durations/{$duration->id}");

        $response->assertOk()
            ->assertJson([
                'message' => 'Durée supprimée avec succès',
            ]);

        $this->assertSoftDeleted('duration_definitions', [
            'id' => $duration->id,
        ]);
    }

    public function test_delete_duration_returns_404_when_not_found(): void
    {
        $response = $this->deleteJson('/api/pricing/durations/00000000-0000-0000-0000-000000000000');

        $response->assertStatus(422);
    }

    public function test_delete_duration_soft_deletes(): void
    {
        $duration = DurationDefinitionEloquentModel::factory()->create();

        $this->deleteJson("/api/pricing/durations/{$duration->id}");

        $this->assertDatabaseHas('duration_definitions', [
            'id' => $duration->id,
        ]);

        $this->assertNotNull(
            DurationDefinitionEloquentModel::withTrashed()->find($duration->id)->deleted_at
        );
    }
}
