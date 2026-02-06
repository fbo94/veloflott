<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BikeBrandsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = database_path('seeders/data/bike_brands.csv');

        if (! file_exists($csvFile)) {
            $this->command->error("CSV file not found: {$csvFile}");

            return;
        }

        // Nettoyer les marques existantes
        \DB::table('brands')->delete();

        $this->command->info('Importing bike brands from CSV...');

        $file = fopen($csvFile, 'r');

        // Skip header
        fgetcsv($file);

        $count = 0;
        while (($data = fgetcsv($file)) !== false) {
            if (empty($data[0])) {
                continue;
            }

            \DB::table('brands')->insert([
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'name' => trim($data[0]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $count++;
        }

        fclose($file);

        $this->command->info("✓ Imported {$count} bike brands successfully.");
    }
}
