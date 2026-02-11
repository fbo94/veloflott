<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rend tenant_id NOT NULL après avoir assigné les données existantes à un tenant par défaut.
 *
 * IMPORTANT: Cette migration doit être exécutée après avoir créé un tenant par défaut
 * et mis à jour les données existantes.
 */

return new class extends Migration
{
    /**
     * @var array<string>
     */
    private array $allTenantTables = [
        'brands',
        'categories',
        'models',
        'rates',
        'pricing_classes',
        'duration_definitions',
        'pricing_rates',
        'discount_rules',
        'size_mapping_configurations',
        'rental_settings',
        'deposit_retention_configs',
        'bikes',
        'customers',
        'rentals',
        'rental_items',
        'rental_equipments',
        'maintenances',
        'bike_status_history',
        'rental_pricing_snapshots',
    ];

    public function up(): void
    {
        // 1. Créer ou récupérer le tenant par défaut
        $defaultTenantId = $this->ensureDefaultTenant();

        // 2. Mettre à jour les enregistrements sans tenant_id
        foreach ($this->allTenantTables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                DB::table($table)
                    ->whereNull('tenant_id')
                    ->update(['tenant_id' => $defaultTenantId]);
            }
        }

        // 3. Rendre tenant_id NOT NULL
        foreach ($this->allTenantTables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $blueprint): void {
                    $blueprint->uuid('tenant_id')->nullable(false)->change();
                });
            }
        }
    }

    public function down(): void
    {
        // Remettre tenant_id en nullable
        foreach ($this->allTenantTables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $blueprint): void {
                    $blueprint->uuid('tenant_id')->nullable()->change();
                });
            }
        }
    }

    private function ensureDefaultTenant(): string
    {
        $defaultTenant = DB::table('tenants')
            ->where('slug', 'default')
            ->first();

        if ($defaultTenant !== null) {
            return $defaultTenant->id;
        }

        // Créer le tenant par défaut
        $id = (string) \Illuminate\Support\Str::uuid();

        DB::table('tenants')->insert([
            'id' => $id,
            'name' => 'Default Tenant',
            'slug' => 'default',
            'domain' => null,
            'status' => 'active',
            'contact_email' => null,
            'contact_phone' => null,
            'settings' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }
};
