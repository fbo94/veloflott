<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Pricing\Infrastructure\Persistence\Models\PricingClassEloquentModel;
use Ramsey\Uuid\Uuid;

/**
 * @extends Factory<PricingClassEloquentModel>
 */
final class PricingClassFactory extends Factory
{
    protected $model = PricingClassEloquentModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Uuid::uuid4()->toString(),
            'code' => str_replace('-', '_', $this->faker->unique()->slug(2)),
            'label' => $this->faker->words(2, true),
            'description' => $this->faker->optional()->sentence(),
            'color' => $this->faker->optional()->hexColor(),
            'sort_order' => $this->faker->numberBetween(0, 10),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
