<?php

namespace Database\Seeders;

use Customer\Infrastructure\Persistence\Models\CustomerEloquentModel;
use Fleet\Infrastructure\Persistence\Models\BikeEloquentModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Rental\Domain\BikeCondition;
use Rental\Domain\DepositStatus;
use Rental\Infrastructure\Persistence\Models\RentalEloquentModel;
use Rental\Infrastructure\Persistence\Models\RentalItemEloquentModel;

class RepresentativeRentalsSeeder extends Seeder
{
    /**
     * Crée 50 locations représentatives avec différents statuts
     */
    public function run(): void
    {
        $this->command->info('Seeding representative rentals...');

        // Nettoyer les locations existantes (optionnel)
        RentalItemEloquentModel::truncate();
        RentalEloquentModel::truncate();

        // Récupérer tous les clients et vélos
        $customers = CustomerEloquentModel::all();
        $bikes = BikeEloquentModel::all();

        if ($customers->isEmpty() || $bikes->isEmpty()) {
            $this->command->error('Please seed customers and bikes first!');

            return;
        }

        $durations = ['half_day', 'full_day', 'two_days', 'three_days', 'week'];
        $now = now();

        $rentals = [];

        // 1. 30 locations COMPLETED (terminées)
        for ($i = 0; $i < 30; $i++) {
            $customer = $customers->random();
            $bike = $bikes->random();
            $duration = $durations[array_rand($durations)];

            // Dates dans le passé (terminées il y a 1 à 60 jours)
            $completedDaysAgo = rand(1, 60);
            $rentalDurationDays = match ($duration) {
                'half_day' => 0.5,
                'full_day' => 1,
                'two_days' => 2,
                'three_days' => 3,
                'week' => 7,
            };

            $startDate = $now->copy()->subDays($completedDaysAgo + $rentalDurationDays);
            $expectedReturnDate = $startDate->copy()->addDays($rentalDurationDays);
            $actualReturnDate = $expectedReturnDate->copy()->addHours(rand(-2, 4)); // Retour avec léger décalage

            $dailyRate = rand(30, 80);
            $totalAmount = $dailyRate * $rentalDurationDays;
            $depositAmount = rand(200, 500);

            $rentals[] = [
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
                'deposit_status' => DepositStatus::RELEASED->value,
                'deposit_retained' => null,
                'cancellation_reason' => null,
                'created_at' => $startDate->copy()->subHours(rand(1, 48)),
                'updated_at' => $actualReturnDate,
                'bike' => $bike,
                'daily_rate' => $dailyRate,
            ];
        }

        // 2. 5 locations CANCELLED (annulées)
        for ($i = 0; $i < 5; $i++) {
            $customer = $customers->random();
            $bike = $bikes->random();
            $duration = $durations[array_rand($durations)];

            // Dates prévues dans le futur ou récent passé
            $startDate = $now->copy()->addDays(rand(-5, 15));
            $rentalDurationDays = match ($duration) {
                'half_day' => 0.5,
                'full_day' => 1,
                'two_days' => 2,
                'three_days' => 3,
                'week' => 7,
            };
            $expectedReturnDate = $startDate->copy()->addDays($rentalDurationDays);

            $dailyRate = rand(30, 80);
            $totalAmount = $dailyRate * $rentalDurationDays;
            $depositAmount = rand(200, 500);

            $cancellationReasons = [
                'Client a changé ses plans de voyage',
                'Problème de santé - certificat médical fourni',
                'Mauvaises conditions météorologiques annoncées',
                'Vélo demandé non disponible - client a préféré annuler',
                'Erreur de réservation - doublon',
            ];

            $rentals[] = [
                'id' => Str::uuid()->toString(),
                'customer_id' => $customer->id,
                'start_date' => $startDate,
                'expected_return_date' => $expectedReturnDate,
                'actual_return_date' => null,
                'duration' => $duration,
                'deposit_amount' => $depositAmount,
                'total_amount' => $totalAmount,
                'discount_amount' => 0,
                'tax_rate' => 20.00,
                'tax_amount' => $totalAmount * 0.20,
                'total_with_tax' => $totalAmount * 1.20,
                'status' => 'cancelled',
                'deposit_status' => DepositStatus::RELEASED->value,
                'deposit_retained' => null,
                'cancellation_reason' => $cancellationReasons[array_rand($cancellationReasons)],
                'created_at' => $startDate->copy()->subDays(rand(1, 10)),
                'updated_at' => $startDate->copy()->subDays(rand(0, 5)),
                'bike' => $bike,
                'daily_rate' => $dailyRate,
            ];
        }

        // 3. 10 locations ACTIVE normales (en cours, sans retard)
        for ($i = 0; $i < 10; $i++) {
            $customer = $customers->random();
            $bike = $bikes->random();
            $duration = $durations[array_rand($durations)];

            // Commencé il y a quelques jours, retour prévu dans le futur
            $startedDaysAgo = rand(1, 5);
            $startDate = $now->copy()->subDays($startedDaysAgo);
            $rentalDurationDays = match ($duration) {
                'half_day' => 0.5,
                'full_day' => 1,
                'two_days' => 2,
                'three_days' => 3,
                'week' => 7,
            };
            $expectedReturnDate = $startDate->copy()->addDays($rentalDurationDays);

            $dailyRate = rand(30, 80);
            $totalAmount = $dailyRate * $rentalDurationDays;
            $depositAmount = rand(200, 500);

            $rentals[] = [
                'id' => Str::uuid()->toString(),
                'customer_id' => $customer->id,
                'start_date' => $startDate,
                'expected_return_date' => $expectedReturnDate,
                'actual_return_date' => null,
                'duration' => $duration,
                'deposit_amount' => $depositAmount,
                'total_amount' => $totalAmount,
                'discount_amount' => 0,
                'tax_rate' => 20.00,
                'tax_amount' => $totalAmount * 0.20,
                'total_with_tax' => $totalAmount * 1.20,
                'status' => 'active',
                'deposit_status' => 'held',
                'deposit_retained' => null,
                'cancellation_reason' => null,
                'created_at' => $startDate->copy()->subHours(rand(1, 24)),
                'updated_at' => $startDate,
                'bike' => $bike,
                'daily_rate' => $dailyRate,
            ];
        }

        // 4. 2 locations ACTIVE en RETARD
        for ($i = 0; $i < 2; $i++) {
            $customer = $customers->random();
            $bike = $bikes->random();
            $duration = $durations[array_rand($durations)];

            // Retour prévu dans le passé (retard de 1 à 7 jours)
            $overdueDays = rand(1, 7);
            $rentalDurationDays = match ($duration) {
                'half_day' => 0.5,
                'full_day' => 1,
                'two_days' => 2,
                'three_days' => 3,
                'week' => 7,
            };

            $expectedReturnDate = $now->copy()->subDays($overdueDays);
            $startDate = $expectedReturnDate->copy()->subDays($rentalDurationDays);

            $dailyRate = rand(30, 80);
            $totalAmount = $dailyRate * $rentalDurationDays;
            $depositAmount = rand(200, 500);

            $rentals[] = [
                'id' => Str::uuid()->toString(),
                'customer_id' => $customer->id,
                'start_date' => $startDate,
                'expected_return_date' => $expectedReturnDate,
                'actual_return_date' => null,
                'duration' => $duration,
                'deposit_amount' => $depositAmount,
                'total_amount' => $totalAmount,
                'discount_amount' => 0,
                'tax_rate' => 20.00,
                'tax_amount' => $totalAmount * 0.20,
                'total_with_tax' => $totalAmount * 1.20,
                'status' => 'active',
                'deposit_status' => 'held',
                'deposit_retained' => null,
                'cancellation_reason' => null,
                'created_at' => $startDate->copy()->subHours(rand(1, 24)),
                'updated_at' => $startDate,
                'bike' => $bike,
                'daily_rate' => $dailyRate,
                'is_overdue' => true,
            ];
        }

        // 5. 3 locations ACTIVE retour prévu dans 3 jours
        for ($i = 0; $i < 3; $i++) {
            $customer = $customers->random();
            $bike = $bikes->random();
            $duration = $durations[array_rand($durations)];

            // Retour prévu dans exactement 3 jours
            $expectedReturnDate = $now->copy()->addDays(3);
            $rentalDurationDays = match ($duration) {
                'half_day' => 0.5,
                'full_day' => 1,
                'two_days' => 2,
                'three_days' => 3,
                'week' => 7,
            };
            $startDate = $expectedReturnDate->copy()->subDays($rentalDurationDays);

            $dailyRate = rand(30, 80);
            $totalAmount = $dailyRate * $rentalDurationDays;
            $depositAmount = rand(200, 500);

            $rentals[] = [
                'id' => Str::uuid()->toString(),
                'customer_id' => $customer->id,
                'start_date' => $startDate,
                'expected_return_date' => $expectedReturnDate,
                'actual_return_date' => null,
                'duration' => $duration,
                'deposit_amount' => $depositAmount,
                'total_amount' => $totalAmount,
                'discount_amount' => 0,
                'tax_rate' => 20.00,
                'tax_amount' => $totalAmount * 0.20,
                'total_with_tax' => $totalAmount * 1.20,
                'status' => 'active',
                'deposit_status' => 'held',
                'deposit_retained' => null,
                'cancellation_reason' => null,
                'created_at' => $startDate->copy()->subHours(rand(1, 24)),
                'updated_at' => $startDate,
                'bike' => $bike,
                'daily_rate' => $dailyRate,
            ];
        }

        // Insérer toutes les locations
        $statusCounts = [
            'completed' => 0,
            'cancelled' => 0,
            'active' => 0,
            'overdue' => 0,
        ];

        foreach ($rentals as $rentalData) {
            $bike = $rentalData['bike'];
            $dailyRate = $rentalData['daily_rate'];
            $isOverdue = $rentalData['is_overdue'] ?? false;
            unset($rentalData['bike'], $rentalData['daily_rate'], $rentalData['is_overdue']);

            // Créer la location
            $rental = RentalEloquentModel::create($rentalData);

            // Créer l'item de location (le vélo)
            RentalItemEloquentModel::create([
                'id' => Str::uuid()->toString(),
                'rental_id' => $rental->id,
                'bike_id' => $bike->id,
                'daily_rate' => $dailyRate,
                'quantity' => 1,
                'client_height' => rand(150, 190),
                'client_weight' => rand(50, 95),
                'saddle_height' => rand(65, 85),
                'front_suspension_pressure' => $bike->front_suspension ? rand(60, 120) : null,
                'rear_suspension_pressure' => $bike->rear_suspension ? rand(60, 120) : null,
                'pedal_type' => ['flat', 'clipless', 'mixed'][array_rand(['flat', 'clipless', 'mixed'])],
                'check_in_notes' => null,
                'return_condition' => $rentalData['status'] === 'completed' ? [BikeCondition::OK->value, BikeCondition::MINOR_DAMAGE->value, BikeCondition::MAJOR_DAMAGE->value][array_rand([0, 1, 2])] : null,
                'damage_description' => null,
                'damage_photos' => null,
                'created_at' => $rentalData['created_at'],
                'updated_at' => $rentalData['updated_at'],
            ]);

            // Comptabiliser
            if ($isOverdue) {
                $statusCounts['overdue']++;
            } else {
                $statusCounts[$rentalData['status']]++;
            }

            $statusLabel = $isOverdue ? 'OVERDUE' : strtoupper($rentalData['status']);
            $this->command->info("✓ Created: {$rental->id} - {$statusLabel} - Customer: {$rentalData['customer_id']}");
        }

        $total = array_sum($statusCounts);
        $this->command->info("✓ Successfully seeded {$total} rentals!");
        $this->command->info("   - Completed: {$statusCounts['completed']}");
        $this->command->info("   - Cancelled: {$statusCounts['cancelled']}");
        $this->command->info("   - Active: {$statusCounts['active']}");
        $this->command->info("   - Overdue: {$statusCounts['overdue']}");
    }
}
