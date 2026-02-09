<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Pricing\Infrastructure\Persistence\Models\PricingRateEloquentModel;
use Ramsey\Uuid\Uuid;

/**
 * @extends Factory<PricingRateEloquentModel>
 */
final class PricingRateFactory extends Factory
{
    protected $model = PricingRateEloquentModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Uuid::uuid4()->toString(),
            'category_id' => Uuid::uuid4()->toString(),
            'pricing_class_id' => Uuid::uuid4()->toString(),
            'duration_id' => Uuid::uuid4()->toString(),
            'price' => $this->faker->randomFloat(2, 10, 200),
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
