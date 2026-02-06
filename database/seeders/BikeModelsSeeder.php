<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BikeModelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = database_path('seeders/data/bike_models.csv');

        if (! file_exists($csvFile)) {
            $this->command->error("CSV file not found: {$csvFile}");

            return;
        }

        // Nettoyer les modèles existants
        \DB::table('models')->delete();

        $this->command->info('Importing bike models from CSV...');

        // Indexer les marques par nom pour accès rapide
        $brandsByName = \DB::table('brands')
            ->get()
            ->keyBy('name')
            ->toArray();

        $file = fopen($csvFile, 'r');

        // Skip header
        fgetcsv($file);

        $count = 0;
        $skipped = 0;
        $missingBrands = [];

        while (($data = fgetcsv($file)) !== false) {
            if (empty($data[0]) || empty($data[1])) {
                continue;
            }

            $brandName = trim($data[0]);
            $modelName = trim($data[1]);

            // Chercher la marque
            if (! isset($brandsByName[$brandName])) {
                if (! in_array($brandName, $missingBrands)) {
                    $missingBrands[] = $brandName;
                }
                $skipped++;

                continue;
            }

            $brand = $brandsByName[$brandName];

            \DB::table('models')->insert([
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'name' => $modelName,
                'brand_id' => $brand->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $count++;
        }

        fclose($file);

        $this->command->info("✓ Imported {$count} bike models successfully.");

        if ($skipped > 0) {
            $this->command->warn("⚠ Skipped {$skipped} models (brand not found)");
            if (! empty($missingBrands)) {
                $this->command->warn('Missing brands: '.implode(', ', $missingBrands));
            }
        }
    }
}
