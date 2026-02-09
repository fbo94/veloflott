<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Pricing\Infrastructure\Persistence\Models\DurationDefinitionEloquentModel;
use Ramsey\Uuid\Uuid;

/**
 * @extends Factory<DurationDefinitionEloquentModel>
 */
final class DurationDefinitionFactory extends Factory
{
    protected $model = DurationDefinitionEloquentModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Uuid::uuid4()->toString(),
            'code' => str_replace('-', '_', $this->faker->unique()->slug(2)),
            'label' => $this->faker->words(2, true),
            'duration_hours' => null,
            'duration_days' => $this->faker->numberBetween(1, 30),
            'is_custom' => false,
            'sort_order' => $this->faker->numberBetween(0, 10),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function withHours(int $hours): static
    {
        return $this->state(fn (array $attributes) => [
            'duration_hours' => $hours,
            'duration_days' => null,
        ]);
    }

    public function custom(): static
    {
        return $this->state(fn (array $attributes) => [
            'duration_hours' => null,
            'duration_days' => null,
            'is_custom' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
