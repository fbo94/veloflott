<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL permet d'ajouter des valeurs à un enum existant
        // Mais Laravel utilise un VARCHAR pour les enums, donc pas de changement nécessaire
        // Le status 'reserved' sera simplement accepté comme nouvelle valeur

        // Ajout d'index pour optimiser les requêtes de disponibilité
        Schema::table('rentals', function (Blueprint $table) {
            // Index composite pour recherche de disponibilité par dates
            $table->index(['start_date', 'expected_return_date'], 'rentals_dates_index');
        });

        // Index pour les maintenances planifiées
        Schema::table('maintenances', function (Blueprint $table) {
            $table->index(['scheduled_at', 'status'], 'maintenances_scheduled_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropIndex('rentals_dates_index');
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropIndex('maintenances_scheduled_status_index');
        });
    }
};
