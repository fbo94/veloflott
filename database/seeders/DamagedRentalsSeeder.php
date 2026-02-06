<?php

namespace Database\Seeders;

use Customer\Infrastructure\Persistence\Models\CustomerEloquentModel;
use Fleet\Infrastructure\Persistence\Models\BikeEloquentModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Rental\Infrastructure\Persistence\Models\RentalEloquentModel;
use Rental\Infrastructure\Persistence\Models\RentalItemEloquentModel;

class DamagedRentalsSeeder extends Seeder
{
    /**
     * Crée 6 locations terminées avec dégâts (5 mineurs, 1 majeur)
     */
    public function run(): void
    {
        $this->command->info('Seeding damaged rentals...');

        // Récupérer des clients et vélos
        $customers = CustomerEloquentModel::all();
        $bikes = BikeEloquentModel::all();

        if ($customers->isEmpty() || $bikes->isEmpty()) {
            $this->command->error('Please seed customers and bikes first!');

            return;
        }

        $now = now();
        $rentals = [];

        // 1. LOCATION AVEC DÉGÂT MAJEUR (1 location)

        $customer = $customers->random();
        $bike = $bikes->random();
        $duration = 'week';
        $rentalDurationDays = 7;

        // Terminée il y a 5 jours
        $completedDaysAgo = 5;
        $startDate = $now->copy()->subDays($completedDaysAgo + $rentalDurationDays);
        $expectedReturnDate = $startDate->copy()->addDays($rentalDurationDays);
        $actualReturnDate = $expectedReturnDate->copy()->addHours(3);

        $dailyRate = 75.00;
        $totalAmount = $dailyRate * $rentalDurationDays;
        $depositAmount = 500.00;
        $depositRetained = 450.00; // Presque toute la caution retenue

        $rentals[] = [
            'rental' => [
                'id' => Str::uuid()->toString(),
                'customer_id' => $customer->id,
                'start_date' => $startDate,
                'expected_return_date' => $expectedReturnDate,
                'actual_return_date' => $actualReturnDate,
                'duration' => $duration,
                'deposit_amount' => $depositAmount,
                'total_amount' => $totalAmount,
                'discount_amount' => 0,
                'tax_rate' => 20.00,
                'tax_amount' => $totalAmount * 0.20,
                'total_with_tax' => $totalAmount * 1.20,
                'status' => 'completed',
                'deposit_status' => 'partial', // Partiellement retenue
                'deposit_retained' => $depositRetained,
                'cancellation_reason' => null,
                'created_at' => $startDate->copy()->subHours(12),
                'updated_at' => $actualReturnDate,
            ],
            'item' => [
                'bike_id' => $bike->id,
                'daily_rate' => $dailyRate,
                'return_condition' => 'major_damage',
                'damage_description' => 'DÉGÂTS MAJEURS : Cadre fissuré au niveau du tube diagonal (impact violent), patte de dérailleur arrière arrachée, roue arrière voilée (rayons cassés), manette de frein avant cassée. Vélo non roulable en l\'état. Nécessite réparations importantes ou remplacement du cadre. Client a reconnu avoir chuté lourdement dans une descente technique.',
            ],
            'type' => 'MAJOR',
        ];

        // 2. 5 LOCATIONS AVEC DÉGÂTS MINEURS
        $minorDamages = [
            [
                'damage_description' => 'Rayures superficielles sur le cadre (tube supérieur et bases arrière), protection de base légèrement abîmée. Pas d\'impact sur la structure. Vélo entièrement fonctionnel.',
                'deposit_retained' => 80.00,
            ],
            [
                'damage_description' => 'Pneu avant crevé (trou de 3mm), chambre à air à remplacer. Légère usure prématurée des patins de frein. Guidoline gauche décollée sur 5cm.',
                'deposit_retained' => 65.00,
            ],
            [
                'damage_description' => 'Cassette arrière usée (sauts de chaîne sur les pignons 3-4), chaîne étirée au-delà de la limite recommandée. Transmission nécessite remplacement. Selle légèrement déchirée (2cm sur le côté droit).',
                'deposit_retained' => 120.00,
            ],
            [
                'damage_description' => 'Pédales rayées et griffées, embout de guidon droit manquant. Garde-boue avant tordu (réparable). Béquille desserrée. Vélo entièrement fonctionnel malgré ces dégâts esthétiques.',
                'deposit_retained' => 55.00,
            ],
            [
                'damage_description' => 'Porte-bidon arraché (visserie arrachée du cadre), nécessite hélicoil. Câble de frein arrière effiloché (à remplacer par sécurité). Grips de guidon déchirés. Compteur rayé.',
                'deposit_retained' => 95.00,
            ],
        ];

        foreach ($minorDamages as $damageData) {
            $customer = $customers->random();
            $bike = $bikes->random();
            $duration = ['two_days', 'three_days', 'week'][array_rand(['two_days', 'three_days', 'week'])];
            $rentalDurationDays = match ($duration) {
                'two_days' => 2,
                'three_days' => 3,
                'week' => 7,
            };

            // Terminées il y a 2 à 20 jours
            $completedDaysAgo = rand(2, 20);
            $startDate = $now->copy()->subDays($completedDaysAgo + $rentalDurationDays);
            $expectedReturnDate = $startDate->copy()->addDays($rentalDurationDays);
            $actualReturnDate = $expectedReturnDate->copy()->addHours(rand(-1, 2));

            $dailyRate = rand(40, 70);
            $totalAmount = $dailyRate * $rentalDurationDays;
            $depositAmount = rand(300, 400);

            $rentals[] = [
                'rental' => [
                    'id' => Str::uuid()->toString(),
                    'customer_id' => $customer->id,
                    'start_date' => $startDate,
                    'expected_return_date' => $expectedReturnDate,
                    'actual_return_date' => $actualReturnDate,
                    'duration' => $duration,
                    'deposit_amount' => $depositAmount,
                    'total_amount' => $totalAmount,
                    'discount_amount' => 0,
                    'tax_rate' => 20.00,
                    'tax_amount' => $totalAmount * 0.20,
                    'total_with_tax' => $totalAmount * 1.20,
                    'status' => 'completed',
                    'deposit_status' => 'partial', // Partiellement retenue
                    'deposit_retained' => $damageData['deposit_retained'],
                    'cancellation_reason' => null,
                    'created_at' => $startDate->copy()->subHours(rand(6, 48)),
                    'updated_at' => $actualReturnDate,
                ],
                'item' => [
                    'bike_id' => $bike->id,
                    'daily_rate' => $dailyRate,
                    'return_condition' => 'minor_damage',
                    'damage_description' => $damageData['damage_description'],
                ],
                'type' => 'MINOR',
            ];
        }

        // Insérer toutes les locations
        $minorCount = 0;
        $majorCount = 0;

        foreach ($rentals as $rentalData) {
            $rentalArray = $rentalData['rental'];
            $itemData = $rentalData['item'];
            $type = $rentalData['type'];

            // Créer la location
            $rental = RentalEloquentModel::create($rentalArray);

            // Créer l'item de location avec les dégâts
            RentalItemEloquentModel::create([
                'id' => Str::uuid()->toString(),
                'rental_id' => $rental->id,
                'bike_id' => $itemData['bike_id'],
                'daily_rate' => $itemData['daily_rate'],
                'quantity' => 1,
                'client_height' => rand(155, 190),
                'client_weight' => rand(55, 95),
                'saddle_height' => rand(68, 85),
                'front_suspension_pressure' => rand(60, 120),
                'rear_suspension_pressure' => rand(60, 120),
                'pedal_type' => ['flat', 'clipless'][array_rand(['flat', 'clipless'])],
                'check_in_notes' => 'Vélo en excellent état au départ',
                'return_condition' => $itemData['return_condition'],
                'damage_description' => $itemData['damage_description'],
                'damage_photos' => null,
                'created_at' => $rentalArray['created_at'],
                'updated_at' => $rentalArray['updated_at'],
            ]);

            if ($type === 'MAJOR') {
                $majorCount++;
                $this->command->error("✓ Created: {$rental->id} - MAJOR DAMAGE - Deposit retained: {$rentalArray['deposit_retained']}€");
            } else {
                $minorCount++;
                $this->command->warn("✓ Created: {$rental->id} - MINOR DAMAGE - Deposit retained: {$rentalArray['deposit_retained']}€");
            }
        }

        $total = $minorCount + $majorCount;
        $this->command->info("✓ Successfully seeded {$total} damaged rentals!");
        $this->command->info("   - Minor damages: {$minorCount}");
        $this->command->info("   - Major damages: {$majorCount}");
    }
}
