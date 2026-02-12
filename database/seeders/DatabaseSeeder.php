<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SubscriptionPlanSeeder::class,
            RepresentativeCustomersSeeder::class,
            BikeBrandsSeeder::class,
            BikeCategoriesSeeder::class,
            BikeModelsSeeder::class,
            PricingSystemSeeder::class,  // Must run after categories
            RepresentativeBikesSeeder::class,
            RepresentativeRentalsSeeder::class,
            DamagedRentalsSeeder::class,
        ]);
    }
}
