<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute tenant_id et site_id aux tables métier pour le multi-tenant.
 *
 * tenant_id: Obligatoire (NOT NULL) - isolation des données par tenant
 * site_id: Optionnel (NULLABLE) - filtrage par site au sein d'un tenant
 */

return new class extends Migration
{
    /**
     * Tables avec tenant_id uniquement (données globales au tenant).
     *
     * @var array<string>
     */
    private array $tenantOnlyTables = [
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
    ];

    /**
     * Tables avec tenant_id ET site_id (données localisées par site).
     *
     * @var array<string>
     */
    private array $tenantAndSiteTables = [
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
        // Tables avec tenant_id uniquement
        foreach ($this->tenantOnlyTables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $table): void {
                    $table->uuid('tenant_id')
                        ->after('id')
                        ->nullable(); // Temporairement nullable pour la migration des données existantes

                    $table->foreign('tenant_id')
                        ->references('id')
                        ->on('tenants')
                        ->onDelete('cascade');

                    $table->index('tenant_id');
                });
            }
        }

        // Tables avec tenant_id ET site_id
        foreach ($this->tenantAndSiteTables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $blueprint) use ($table): void {
                    if (!Schema::hasColumn($table, 'tenant_id')) {
                        $blueprint->uuid('tenant_id')
                            ->after('id')
                            ->nullable(); // Temporairement nullable

                        $blueprint->foreign('tenant_id')
                            ->references('id')
                            ->on('tenants')
                            ->onDelete('cascade');

                        $blueprint->index('tenant_id');
                    }

                    if (!Schema::hasColumn($table, 'site_id')) {
                        $blueprint->uuid('site_id')
                            ->after('tenant_id')
                            ->nullable(); // Site est toujours optionnel

                        $blueprint->foreign('site_id')
                            ->references('id')
                            ->on('sites')
                            ->onDelete('set null');

                        $blueprint->index('site_id');
                    }
                });
            }
        }
    }

    public function down(): void
    {
        // Tables avec tenant_id ET site_id
        foreach ($this->tenantAndSiteTables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $blueprint) use ($table): void {
                    if (Schema::hasColumn($table, 'site_id')) {
                        $blueprint->dropForeign([$table . '_site_id_foreign']);
                        $blueprint->dropIndex([$table . '_site_id_index']);
                        $blueprint->dropColumn('site_id');
                    }

                    if (Schema::hasColumn($table, 'tenant_id')) {
                        $blueprint->dropForeign([$table . '_tenant_id_foreign']);
                        $blueprint->dropIndex([$table . '_tenant_id_index']);
                        $blueprint->dropColumn('tenant_id');
                    }
                });
            }
        }

        // Tables avec tenant_id uniquement
        foreach ($this->tenantOnlyTables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $blueprint) use ($table): void {
                    $blueprint->dropForeign([$table . '_tenant_id_foreign']);
                    $blueprint->dropIndex([$table . '_tenant_id_index']);
                    $blueprint->dropColumn('tenant_id');
                });
            }
        }
    }
};
