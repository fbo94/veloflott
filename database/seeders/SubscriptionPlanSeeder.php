<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

final class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Seed subscription plans.
     */
    public function run(): void
    {
        // Vérifier si des plans existent déjà
        if (DB::table('subscription_plans')->count() > 0) {
            $this->command->info('⚠️  Des plans d\'abonnement existent déjà. Skipping...');

            return;
        }

        $now = now();

        $plans = [
            [
                'id' => Uuid::uuid4()->toString(),
                'name' => 'starter',
                'display_name' => 'Starter',
                'description' => 'Idéal pour démarrer et tester la plateforme',
                'price_monthly' => 29.00,
                'price_yearly' => 290.00,
                'max_users' => 5,
                'max_bikes' => 50,
                'max_sites' => 1,
                'features' => json_encode([
                    'basic_analytics' => true,
                    'advanced_analytics' => false,
                    'api_access' => false,
                    'custom_branding' => false,
                    'priority_support' => false,
                ]),
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => Uuid::uuid4()->toString(),
                'name' => 'pro',
                'display_name' => 'Pro',
                'description' => 'Pour les structures en croissance',
                'price_monthly' => 99.00,
                'price_yearly' => 990.00,
                'max_users' => 25,
                'max_bikes' => 250,
                'max_sites' => 5,
                'features' => json_encode([
                    'basic_analytics' => true,
                    'advanced_analytics' => true,
                    'api_access' => true,
                    'custom_branding' => false,
                    'priority_support' => true,
                ]),
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => Uuid::uuid4()->toString(),
                'name' => 'enterprise',
                'display_name' => 'Enterprise',
                'description' => 'Solution sur-mesure pour les grandes organisations',
                'price_monthly' => null,
                'price_yearly' => null,
                'max_users' => 999999,
                'max_bikes' => 999999,
                'max_sites' => 999999,
                'features' => json_encode([
                    'basic_analytics' => true,
                    'advanced_analytics' => true,
                    'api_access' => true,
                    'custom_branding' => true,
                    'priority_support' => true,
                    'dedicated_account_manager' => true,
                    'custom_integrations' => true,
                    'sla_guarantee' => true,
                ]),
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('subscription_plans')->insert($plans);

        $this->command->info('✅ 3 plans d\'abonnement créés avec succès (Starter, Pro, Enterprise)');
    }
}
