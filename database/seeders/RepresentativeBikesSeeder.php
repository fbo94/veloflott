<?php

namespace Database\Seeders;

use Fleet\Infrastructure\Persistence\Models\BikeEloquentModel;
use Fleet\Infrastructure\Persistence\Models\BrandEloquentModel;
use Fleet\Infrastructure\Persistence\Models\CategoryEloquentModel;
use Fleet\Infrastructure\Persistence\Models\ModelEloquentModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RepresentativeBikesSeeder extends Seeder
{
    /**
     * Liste de 11 vélos représentatifs du marché
     */
    public function run(): void
    {
        $this->command->info('Seeding representative bikes...');

        // Nettoyer les vélos existants du seeder (optionnel)
        BikeEloquentModel::where('internal_number', 'like', 'BIKE-%')->delete();

        $bikes = [
            // ROUTE (3 vélos)
            [
                'brand' => 'Specialized',
                'model' => 'Tarmac SL7',
                'category_slug' => 'route-classique',
                'frame_size_cm' => 54,
                'frame_size_inches' => null,
                'frame_size_letter' => 'm',
                'color' => 'Red',
                'year' => 2024,
                'purchase_price' => 6500.00,
                'serial_number' => 'SPEC-TARMAC-'.Str::random(8),
            ],
            [
                'brand' => 'Trek',
                'model' => 'Émonda SLR',
                'category_slug' => 'route-classique',
                'frame_size_cm' => 56,
                'frame_size_inches' => null,
                'frame_size_letter' => 'm',
                'color' => 'Black',
                'year' => 2024,
                'purchase_price' => 7200.00,
                'serial_number' => 'TREK-EMONDA-'.Str::random(8),
            ],
            [
                'brand' => 'Cannondale',
                'model' => 'SuperSix EVO',
                'category_slug' => 'route-classique',
                'frame_size_cm' => 54,
                'frame_size_inches' => null,
                'frame_size_letter' => 'm',
                'color' => 'Green',
                'year' => 2024,
                'purchase_price' => 5800.00,
                'serial_number' => 'CANN-SSEVO-'.Str::random(8),
            ],

            // TRIATHLON (1 vélo)
            [
                'brand' => 'Cervélo',
                'model' => 'P-Series',
                'category_slug' => 'clm',
                'frame_size_cm' => 54,
                'frame_size_inches' => null,
                'frame_size_letter' => 'm',
                'color' => 'White',
                'year' => 2024,
                'purchase_price' => 8000.00,
                'serial_number' => 'CERV-PSERIES-'.Str::random(8),
            ],

            // VTT (4 vélos)
            [
                'brand' => 'Giant',
                'model' => 'Trance X',
                'category_slug' => 'trail',
                'frame_size_cm' => null,
                'frame_size_inches' => 17.5,
                'frame_size_letter' => 'm',
                'color' => 'Blue',
                'year' => 2024,
                'purchase_price' => 4200.00,
                'front_suspension' => 140,
                'rear_suspension' => 140,
                'wheel_size' => '29',
                'brake_type' => 'hydraulic_disc',
                'serial_number' => 'GIANT-TRANCE-'.Str::random(8),
            ],
            [
                'brand' => 'Santa Cruz',
                'model' => 'Hightower',
                'category_slug' => 'trail',
                'frame_size_cm' => null,
                'frame_size_inches' => 18,
                'frame_size_letter' => 'm',
                'color' => 'Orange',
                'year' => 2024,
                'purchase_price' => 7000.00,
                'front_suspension' => 145,
                'rear_suspension' => 145,
                'wheel_size' => '29',
                'brake_type' => 'hydraulic_disc',
                'serial_number' => 'SC-HIGHT-'.Str::random(8),
            ],
            [
                'brand' => 'Trek',
                'model' => 'Fuel EX',
                'category_slug' => 'trail',
                'frame_size_cm' => null,
                'frame_size_inches' => 17.5,
                'frame_size_letter' => 'm',
                'color' => 'Gray',
                'year' => 2024,
                'purchase_price' => 5200.00,
                'front_suspension' => 140,
                'rear_suspension' => 140,
                'wheel_size' => '29',
                'brake_type' => 'hydraulic_disc',
                'serial_number' => 'TREK-FUELEX-'.Str::random(8),
            ],
            [
                'brand' => 'Specialized',
                'model' => 'Stumpjumper',
                'category_slug' => 'trail',
                'frame_size_cm' => null,
                'frame_size_inches' => 18,
                'frame_size_letter' => 'm',
                'color' => 'Black',
                'year' => 2024,
                'purchase_price' => 6100.00,
                'front_suspension' => 150,
                'rear_suspension' => 150,
                'wheel_size' => '29',
                'brake_type' => 'hydraulic_disc',
                'serial_number' => 'SPEC-STUMP-'.Str::random(8),
            ],

            // GRAVEL (3 vélos)
            [
                'brand' => 'Cannondale',
                'model' => 'Topstone Carbon',
                'category_slug' => 'gravel',
                'frame_size_cm' => 54,
                'frame_size_inches' => null,
                'frame_size_letter' => 'm',
                'color' => 'Brown',
                'year' => 2024,
                'purchase_price' => 4200.00,
                'front_suspension' => 30,
                'wheel_size' => '700c',
                'brake_type' => 'hydraulic_disc',
                'serial_number' => 'CANN-TOPSTONE-'.Str::random(8),
            ],
            [
                'brand' => 'Specialized',
                'model' => 'Diverge',
                'category_slug' => 'gravel',
                'frame_size_cm' => 56,
                'frame_size_inches' => null,
                'frame_size_letter' => 'm',
                'color' => 'Green',
                'year' => 2024,
                'purchase_price' => 4800.00,
                'wheel_size' => '700c',
                'brake_type' => 'hydraulic_disc',
                'serial_number' => 'SPEC-DIVERGE-'.Str::random(8),
            ],
            [
                'brand' => 'Giant',
                'model' => 'Revolt Advanced',
                'category_slug' => 'gravel',
                'frame_size_cm' => 54,
                'frame_size_inches' => null,
                'frame_size_letter' => 'm',
                'color' => 'Blue',
                'year' => 2024,
                'purchase_price' => 3200.00,
                'wheel_size' => '700c',
                'brake_type' => 'hydraulic_disc',
                'serial_number' => 'GIANT-REVOLT-'.Str::random(8),
            ],
        ];

        $counter = 1;
        foreach ($bikes as $bikeData) {
            // 1. Créer ou récupérer la marque
            $brand = BrandEloquentModel::firstOrCreate(
                ['name' => $bikeData['brand']],
                ['created_at' => now(), 'updated_at' => now()]
            );

            // 2. Créer le modèle
            $model = ModelEloquentModel::firstOrCreate(
                [
                    'name' => $bikeData['model'],
                    'brand_id' => $brand->id,
                ],
                ['created_at' => now(), 'updated_at' => now()]
            );

            // 3. Récupérer la catégorie
            $category = CategoryEloquentModel::where('slug', $bikeData['category_slug'])->first();

            if (! $category) {
                $this->command->error("Category '{$bikeData['category_slug']}' not found for {$bikeData['brand']} {$bikeData['model']}");

                continue;
            }

            // 4. Créer le vélo
            $internalNumber = sprintf('BIKE-%03d', $counter);

            BikeEloquentModel::create([
                'id' => Str::uuid()->toString(),
                'qr_code_uuid' => Str::uuid()->toString(),
                'internal_number' => $internalNumber,
                'model_id' => $model->id,
                'category_id' => $category->id,
                'frame_size_unit' => $bikeData['frame_size_cm'] ? 'cm' : 'inch',
                'frame_size_numeric' => $bikeData['frame_size_cm'] ?? $bikeData['frame_size_inches'],
                'frame_size_letter' => null,
                'frame_size_letter_equivalent' => $bikeData['frame_size_letter'],
                'status' => 'available',
                'year' => $bikeData['year'],
                'serial_number' => $bikeData['serial_number'],
                'color' => $bikeData['color'],
                'wheel_size' => $bikeData['wheel_size'] ?? null,
                'front_suspension' => $bikeData['front_suspension'] ?? null,
                'rear_suspension' => $bikeData['rear_suspension'] ?? null,
                'brake_type' => $bikeData['brake_type'] ?? null,
                'purchase_price' => $bikeData['purchase_price'],
                'purchase_date' => now()->subMonths(rand(1, 12)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info("✓ Created: {$internalNumber} - {$bikeData['brand']} {$bikeData['model']}");
            $counter++;
        }

        $totalBikes = $counter - 1;
        $this->command->info("✓ Successfully seeded {$totalBikes} representative bikes!");
    }
}
