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
                'name' => 'free',
                'display_name' => 'Plan Gratuit',
                'description' => 'Idéal pour démarrer et tester la plateforme',
                'price_monthly' => null,
                'price_yearly' => null,
                'max_users' => 5,
                'max_bikes' => 50,
                'max_sites' => 1,
                'features' => json_encode([
                    'basic_analytics' => true,
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
                'name' => 'starter',
                'display_name' => 'Plan Starter',
                'description' => 'Pour les petites structures en croissance',
                'price_monthly' => 49.00,
                'price_yearly' => 490.00, // ~2 mois gratuits
                'max_users' => 20,
                'max_bikes' => 200,
                'max_sites' => 3,
                'features' => json_encode([
                    'basic_analytics' => true,
                    'advanced_analytics' => true,
                    'api_access' => true,
                    'custom_branding' => false,
                    'priority_support' => false,
                ]),
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => Uuid::uuid4()->toString(),
                'name' => 'professional',
                'display_name' => 'Plan Professional',
                'description' => 'Pour les entreprises établies',
                'price_monthly' => 199.00,
                'price_yearly' => 1990.00, // ~2 mois gratuits
                'max_users' => 100,
                'max_bikes' => 1000,
                'max_sites' => 10,
                'features' => json_encode([
                    'basic_analytics' => true,
                    'advanced_analytics' => true,
                    'api_access' => true,
                    'custom_branding' => true,
                    'priority_support' => true,
                    'dedicated_account_manager' => false,
                ]),
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => Uuid::uuid4()->toString(),
                'name' => 'enterprise',
                'display_name' => 'Plan Enterprise',
                'description' => 'Solution sur-mesure pour les grandes organisations',
                'price_monthly' => null, // Sur devis
                'price_yearly' => null, // Sur devis
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
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('subscription_plans')->insert($plans);

        $this->command->info('✅ 4 plans d\'abonnement créés avec succès');
    }
}
