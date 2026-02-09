<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeder pour créer des tenants et sites de test.
 *
 * Usage:
 *   php artisan db:seed --class=TenantSeeder
 */
class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // Vérifier si les données existent déjà
        $existingTenants = DB::table('tenants')
            ->whereIn('slug', ['veloflott-paris', 'bikeshare-lyon', 'default'])
            ->count();

        if ($existingTenants > 0) {
            $this->command->info('⚠️  Des tenants existent déjà. Skipping...');
            $this->command->info('   Pour réinitialiser: php artisan migrate:fresh --seed');
            return;
        }

        // Tenant 1: VéloFlott Paris
        $tenant1Id = (string) Str::uuid();
        DB::table('tenants')->insert([
            'id' => $tenant1Id,
            'name' => 'VéloFlott Paris',
            'slug' => 'veloflott-paris',
            'domain' => 'paris.veloflott.fr',
            'status' => 'active',
            'contact_email' => 'contact@paris.veloflott.fr',
            'contact_phone' => '+33 1 23 45 67 89',
            'settings' => json_encode([
                'default_currency' => 'EUR',
                'timezone' => 'Europe/Paris',
                'language' => 'fr',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Sites pour Tenant 1
        $site1_1 = (string) Str::uuid();
        DB::table('sites')->insert([
            'id' => $site1_1,
            'tenant_id' => $tenant1Id,
            'name' => 'Paris Centre',
            'slug' => 'paris-centre',
            'address' => '123 Rue de Rivoli',
            'city' => 'Paris',
            'postal_code' => '75001',
            'country' => 'FR',
            'phone' => '+33 1 23 45 67 89',
            'email' => 'paris-centre@veloflott.fr',
            'status' => 'active',
            'latitude' => 48.8566,
            'longitude' => 2.3522,
            'opening_hours' => json_encode([
                'monday' => ['open' => '09:00', 'close' => '19:00'],
                'tuesday' => ['open' => '09:00', 'close' => '19:00'],
                'wednesday' => ['open' => '09:00', 'close' => '19:00'],
                'thursday' => ['open' => '09:00', 'close' => '19:00'],
                'friday' => ['open' => '09:00', 'close' => '19:00'],
                'saturday' => ['open' => '10:00', 'close' => '18:00'],
                'sunday' => ['open' => '10:00', 'close' => '18:00'],
            ]),
            'settings' => json_encode([
                'capacity' => 100,
                'rental_deposit' => 300,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $site1_2 = (string) Str::uuid();
        DB::table('sites')->insert([
            'id' => $site1_2,
            'tenant_id' => $tenant1Id,
            'name' => 'Paris La Défense',
            'slug' => 'paris-la-defense',
            'address' => '1 Parvis de La Défense',
            'city' => 'La Défense',
            'postal_code' => '92400',
            'country' => 'FR',
            'phone' => '+33 1 23 45 67 90',
            'email' => 'la-defense@veloflott.fr',
            'status' => 'active',
            'latitude' => 48.8923,
            'longitude' => 2.2365,
            'opening_hours' => json_encode([
                'monday' => ['open' => '08:00', 'close' => '20:00'],
                'tuesday' => ['open' => '08:00', 'close' => '20:00'],
                'wednesday' => ['open' => '08:00', 'close' => '20:00'],
                'thursday' => ['open' => '08:00', 'close' => '20:00'],
                'friday' => ['open' => '08:00', 'close' => '20:00'],
                'saturday' => ['open' => '10:00', 'close' => '18:00'],
                'sunday' => null,
            ]),
            'settings' => json_encode([
                'capacity' => 80,
                'rental_deposit' => 300,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Tenant 2: BikeShare Lyon
        $tenant2Id = (string) Str::uuid();
        DB::table('tenants')->insert([
            'id' => $tenant2Id,
            'name' => 'BikeShare Lyon',
            'slug' => 'bikeshare-lyon',
            'domain' => 'lyon.bikeshare.fr',
            'status' => 'active',
            'contact_email' => 'contact@lyon.bikeshare.fr',
            'contact_phone' => '+33 4 12 34 56 78',
            'settings' => json_encode([
                'default_currency' => 'EUR',
                'timezone' => 'Europe/Paris',
                'language' => 'fr',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Sites pour Tenant 2
        $site2_1 = (string) Str::uuid();
        DB::table('sites')->insert([
            'id' => $site2_1,
            'tenant_id' => $tenant2Id,
            'name' => 'Lyon Part-Dieu',
            'slug' => 'lyon-part-dieu',
            'address' => '10 Place Charles Béraudier',
            'city' => 'Lyon',
            'postal_code' => '69003',
            'country' => 'FR',
            'phone' => '+33 4 12 34 56 78',
            'email' => 'part-dieu@bikeshare.fr',
            'status' => 'active',
            'latitude' => 45.7603,
            'longitude' => 4.8591,
            'opening_hours' => json_encode([
                'monday' => ['open' => '09:00', 'close' => '19:00'],
                'tuesday' => ['open' => '09:00', 'close' => '19:00'],
                'wednesday' => ['open' => '09:00', 'close' => '19:00'],
                'thursday' => ['open' => '09:00', 'close' => '19:00'],
                'friday' => ['open' => '09:00', 'close' => '19:00'],
                'saturday' => ['open' => '10:00', 'close' => '18:00'],
                'sunday' => null,
            ]),
            'settings' => json_encode([
                'capacity' => 60,
                'rental_deposit' => 250,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Tenant 3: Default (pour les données de développement)
        // Vérifier si le tenant default existe déjà (créé par la migration)
        $defaultTenant = DB::table('tenants')->where('slug', 'default')->first();

        if ($defaultTenant === null) {
            $defaultTenantId = (string) Str::uuid();
            DB::table('tenants')->insert([
                'id' => $defaultTenantId,
                'name' => 'Default Tenant',
                'slug' => 'default',
                'domain' => null,
                'status' => 'active',
                'contact_email' => 'dev@veloflott.fr',
                'contact_phone' => null,
                'settings' => json_encode([
                    'default_currency' => 'EUR',
                    'timezone' => 'Europe/Paris',
                    'language' => 'fr',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $defaultTenantId = $defaultTenant->id;
        }

        // Vérifier si un site existe déjà pour ce tenant
        $existingSite = DB::table('sites')->where('tenant_id', $defaultTenantId)->first();

        if ($existingSite === null) {
            $defaultSiteId = (string) Str::uuid();
            DB::table('sites')->insert([
                'id' => $defaultSiteId,
                'tenant_id' => $defaultTenantId,
                'name' => 'Site Principal',
                'slug' => 'site-principal',
                'address' => null,
                'city' => null,
                'postal_code' => null,
                'country' => 'FR',
                'phone' => null,
                'email' => null,
                'status' => 'active',
                'latitude' => null,
                'longitude' => null,
                'opening_hours' => null,
                'settings' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✅ Tenants et sites créés avec succès :');
        $this->command->info('   - VéloFlott Paris (2 sites)');
        $this->command->info('   - BikeShare Lyon (1 site)');
        $this->command->info('   - Default Tenant (1 site)');
    }
}
