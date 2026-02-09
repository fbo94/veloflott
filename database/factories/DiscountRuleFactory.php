<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Pricing\Infrastructure\Persistence\Models\DiscountRuleEloquentModel;
use Ramsey\Uuid\Uuid;

/**
 * @extends Factory<DiscountRuleEloquentModel>
 */
final class DiscountRuleFactory extends Factory
{
    protected $model = DiscountRuleEloquentModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Uuid::uuid4()->toString(),
            'category_id' => null,
            'pricing_class_id' => null,
            'min_days' => $this->faker->numberBetween(1, 30),
            'min_duration_id' => null,
            'discount_type' => 'percentage',
            'discount_value' => $this->faker->randomFloat(2, 5, 20),
            'label' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->sentence(),
            'is_cumulative' => false,
            'priority' => $this->faker->numberBetween(0, 10),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function fixed(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_type' => 'fixed',
            'discount_value' => $this->faker->randomFloat(2, 5, 50),
        ]);
    }

    public function forCategory(string $categoryId): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $categoryId,
        ]);
    }

    public function forPricingClass(string $pricingClassId): static
    {
        return $this->state(fn (array $attributes) => [
            'pricing_class_id' => $pricingClassId,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
