<?php

namespace Database\Seeders;

use App\Models\User;
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
        // Note: User model no longer uses factories
        // Users are managed via Keycloak authentication

        $this->call([
            RepresentativeCustomersSeeder::class,
            RepresentativeRentalsSeeder::class,
        ]);
    }
}
