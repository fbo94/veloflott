<?php

declare(strict_types=1);

namespace Database\Seeders;

use Fleet\Infrastructure\Persistence\Models\DiscountRuleEloquentModel;
use Fleet\Infrastructure\Persistence\Models\DurationDefinitionEloquentModel;
use Fleet\Infrastructure\Persistence\Models\PricingClassEloquentModel;
use Fleet\Infrastructure\Persistence\Models\PricingRateEloquentModel;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

final class PricingSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Pricing Classes
        $pricingClasses = $this->seedPricingClasses();

        // 2. Create Duration Definitions
        $durations = $this->seedDurations();

        // 3. Create Pricing Rates (3D Grid)
        $this->seedPricingRates($pricingClasses, $durations);

        // 4. Create Discount Rules
        $this->seedDiscountRules();
    }

    /**
     * @return array<string, PricingClassEloquentModel>
     */
    private function seedPricingClasses(): array
    {
        $classes = [
            'standard' => [
                'label' => 'Standard',
                'description' => 'Vélos classiques en bon état',
                'color' => '#3B82F6',
                'sort_order' => 1,
            ],
            'premium' => [
                'label' => 'Premium',
                'description' => 'Vélos haut de gamme et récents',
                'color' => '#8B5CF6',
                'sort_order' => 2,
            ],
            'elite' => [
                'label' => 'Elite',
                'description' => 'Vélos exceptionnels et modèles exclusifs',
                'color' => '#F59E0B',
                'sort_order' => 3,
            ],
        ];

        $models = [];
        foreach ($classes as $code => $data) {
            $model = PricingClassEloquentModel::updateOrCreate(['code' => $code], [
                'id' => Uuid::uuid4()->toString(),
                'code' => $code,
                'label' => $data['label'],
                'description' => $data['description'],
                'color' => $data['color'],
                'sort_order' => $data['sort_order'],
                'is_active' => true,
            ]);
            $models[$code] = $model;
        }

        return $models;
    }

    /**
     * @return array<string, DurationDefinitionEloquentModel>
     */
    private function seedDurations(): array
    {
        $durations = [
            'half_day' => [
                'label' => 'Demi-journée (4h)',
                'duration_hours' => 4,
                'duration_days' => null,
                'is_custom' => false,
                'sort_order' => 1,
            ],
            'full_day' => [
                'label' => 'Journée',
                'duration_hours' => null,
                'duration_days' => 1,
                'is_custom' => false,
                'sort_order' => 2,
            ],
            'two_days' => [
                'label' => 'Week-end (2 jours)',
                'duration_hours' => null,
                'duration_days' => 2,
                'is_custom' => false,
                'sort_order' => 3,
            ],
            'three_days' => [
                'label' => '3 jours',
                'duration_hours' => null,
                'duration_days' => 3,
                'is_custom' => false,
                'sort_order' => 4,
            ],
            'week' => [
                'label' => 'Semaine',
                'duration_hours' => null,
                'duration_days' => 7,
                'is_custom' => false,
                'sort_order' => 5,
            ],
            'two_weeks' => [
                'label' => '2 semaines',
                'duration_hours' => null,
                'duration_days' => 14,
                'is_custom' => false,
                'sort_order' => 6,
            ],
            'month' => [
                'label' => 'Mois',
                'duration_hours' => null,
                'duration_days' => 30,
                'is_custom' => false,
                'sort_order' => 7,
            ],
            'custom' => [
                'label' => 'Durée personnalisée',
                'duration_hours' => null,
                'duration_days' => null,
                'is_custom' => true,
                'sort_order' => 99,
            ],
        ];

        $models = [];
        foreach ($durations as $code => $data) {
            $model = DurationDefinitionEloquentModel::updateOrCreate(['code' => $code], [
                'id' => Uuid::uuid4()->toString(),
                'code' => $code,
                'label' => $data['label'],
                'duration_hours' => $data['duration_hours'],
                'duration_days' => $data['duration_days'],
                'is_custom' => $data['is_custom'],
                'sort_order' => $data['sort_order'],
                'is_active' => true,
            ]);
            $models[$code] = $model;
        }

        return $models;
    }

    /**
     * @param array<string, PricingClassEloquentModel> $classes
     * @param array<string, DurationDefinitionEloquentModel> $durations
     */
    private function seedPricingRates(array $classes, array $durations): void
    {
        // Get all active categories
        $categories = \DB::table('categories')->get();

        if ($categories->isEmpty()) {
            $this->command->warn('No active categories found. Pricing rates will not be seeded.');
            return;
        }

        // Define base prices for each category type and pricing class
        $basePrices = [
            'VTT' => ['standard' => 25, 'premium' => 40, 'elite' => 60],
            'Route' => ['standard' => 30, 'premium' => 50, 'elite' => 75],
            'Ville' => ['standard' => 15, 'premium' => 25, 'elite' => 40],
            'Électrique' => ['standard' => 35, 'premium' => 55, 'elite' => 85],
        ];

        // Duration multipliers (compared to daily rate)
        $durationMultipliers = [
            'half_day' => 0.6,
            'full_day' => 1.0,
            'two_days' => 1.85,
            'three_days' => 2.7,
            'week' => 6.0,
            'two_weeks' => 11.0,
            'month' => 22.0,
        ];

        foreach ($categories as $category) {
            // Determine base price based on category name
            $categoryBasePrice = $basePrices['VTT']; // default
            foreach ($basePrices as $type => $prices) {
                if (stripos($category->name, $type) !== false) {
                    $categoryBasePrice = $prices;
                    break;
                }
            }

            foreach ($classes as $classCode => $class) {
                $basePrice = $categoryBasePrice[$classCode];

                foreach ($durations as $durationCode => $duration) {
                    if ($durationCode === 'custom') {
                        continue; // Skip custom duration
                    }

                    $multiplier = $durationMultipliers[$durationCode];
                    $price = round($basePrice * $multiplier, 2);

                    PricingRateEloquentModel::create([
                        'id' => Uuid::uuid4()->toString(),
                        'category_id' => $category->id,
                        'pricing_class_id' => $class->id,
                        'duration_id' => $duration->id,
                        'price' => $price,
                        'is_active' => true,
                    ]);
                }
            }
        }

        $this->command->info('Pricing rates seeded successfully.');
    }

    private function seedDiscountRules(): void
    {
        $rules = [
            [
                'category_id' => null,
                'pricing_class_id' => null,
                'min_days' => 7,
                'discount_type' => 'percentage',
                'discount_value' => 10.0,
                'label' => 'Réduction semaine -10%',
                'description' => 'Réduction de 10% pour les locations de 7 jours ou plus',
                'is_cumulative' => false,
                'priority' => 1,
            ],
            [
                'category_id' => null,
                'pricing_class_id' => null,
                'min_days' => 14,
                'discount_type' => 'percentage',
                'discount_value' => 15.0,
                'label' => 'Réduction 2 semaines -15%',
                'description' => 'Réduction de 15% pour les locations de 14 jours ou plus',
                'is_cumulative' => false,
                'priority' => 2,
            ],
            [
                'category_id' => null,
                'pricing_class_id' => null,
                'min_days' => 30,
                'discount_type' => 'percentage',
                'discount_value' => 20.0,
                'label' => 'Réduction mois -20%',
                'description' => 'Réduction de 20% pour les locations de 30 jours ou plus',
                'is_cumulative' => false,
                'priority' => 3,
            ],
        ];

        foreach ($rules as $rule) {
            DiscountRuleEloquentModel::create([
                'id' => Uuid::uuid4()->toString(),
                'category_id' => $rule['category_id'],
                'pricing_class_id' => $rule['pricing_class_id'],
                'min_days' => $rule['min_days'],
                'min_duration_id' => null,
                'discount_type' => $rule['discount_type'],
                'discount_value' => $rule['discount_value'],
                'label' => $rule['label'],
                'description' => $rule['description'],
                'is_cumulative' => $rule['is_cumulative'],
                'priority' => $rule['priority'],
                'is_active' => true,
            ]);
        }

        $this->command->info('Discount rules seeded successfully.');
    }
}
