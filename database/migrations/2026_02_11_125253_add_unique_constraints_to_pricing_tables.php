<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute les contraintes d'unicité tenant-scoped aux tables de pricing.
 *
 * - pricing_classes: (tenant_id, code) unique
 * - duration_definitions: (tenant_id, code) unique
 * - pricing_rates: (tenant_id, category_id, pricing_class_id, duration_id) unique
 * - discount_rules: (tenant_id, label) unique
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. pricing_classes: remplacer unique(code) par unique(tenant_id, code)
        Schema::table('pricing_classes', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->unique(['tenant_id', 'code'], 'pricing_classes_tenant_code_unique');
        });

        // 2. duration_definitions: remplacer unique(code) par unique(tenant_id, code)
        Schema::table('duration_definitions', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->unique(['tenant_id', 'code'], 'duration_definitions_tenant_code_unique');
        });

        // 3. pricing_rates: remplacer unique par (tenant_id, category_id, pricing_class_id, duration_id)
        Schema::table('pricing_rates', function (Blueprint $table) {
            $table->dropUnique('unique_pricing_rate');
            $table->unique(
                ['tenant_id', 'category_id', 'pricing_class_id', 'duration_id'],
                'pricing_rates_tenant_unique'
            );
        });

        // 4. discount_rules: ajouter unique(tenant_id, label)
        Schema::table('discount_rules', function (Blueprint $table) {
            $table->unique(['tenant_id', 'label'], 'discount_rules_tenant_label_unique');
        });
    }

    public function down(): void
    {
        // 1. pricing_classes: restaurer unique(code)
        Schema::table('pricing_classes', function (Blueprint $table) {
            $table->dropUnique('pricing_classes_tenant_code_unique');
            $table->unique(['code']);
        });

        // 2. duration_definitions: restaurer unique(code)
        Schema::table('duration_definitions', function (Blueprint $table) {
            $table->dropUnique('duration_definitions_tenant_code_unique');
            $table->unique(['code']);
        });

        // 3. pricing_rates: restaurer l'ancien unique
        Schema::table('pricing_rates', function (Blueprint $table) {
            $table->dropUnique('pricing_rates_tenant_unique');
            $table->unique(
                ['category_id', 'pricing_class_id', 'duration_id'],
                'unique_pricing_rate'
            );
        });

        // 4. discount_rules: supprimer unique(tenant_id, label)
        Schema::table('discount_rules', function (Blueprint $table) {
            $table->dropUnique('discount_rules_tenant_label_unique');
        });
    }
};
