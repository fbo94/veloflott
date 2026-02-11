<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rend les marques (brands) globales en rendant tenant_id nullable.
 *
 * Les marques sont maintenant partagées entre tous les tenants (Specialized, Trek, Giant...).
 * Les marques existantes gardent leur tenant_id, les nouvelles peuvent être globales (NULL).
 */

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table): void {
            // Rendre tenant_id nullable
            $table->uuid('tenant_id')->nullable()->change();

            // Supprimer l'index et la contrainte FK si besoin
            if (Schema::hasColumn('brands', 'tenant_id')) {
                // Note: Les contraintes seront recréées si nécessaire
                $table->dropForeign(['tenant_id']);
                $table->foreign('tenant_id')
                    ->references('id')
                    ->on('tenants')
                    ->onDelete('set null'); // Si le tenant est supprimé, la marque devient globale
            }
        });
    }

    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table): void {
            // Remettre tenant_id NOT NULL (impossible si des marques globales existent)
            // On garde juste la contrainte FK
            $table->dropForeign(['tenant_id']);
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');
        });
    }
};
