<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rates', function (Blueprint $table) {
            // Supprimer les contraintes UNIQUE existantes
            $table->dropUnique('rates_category_id_unique');
            $table->dropUnique('rates_bike_id_unique');

            // Ajouter la colonne pricing_tier
            $table->enum('pricing_tier', ['standard', 'premium'])
                ->nullable()
                ->after('bike_id')
                ->comment('Tier de pricing (null pour les tarifs spécifiques au vélo)');

            // Créer une nouvelle contrainte UNIQUE composite
            // Une catégorie peut avoir plusieurs tarifs (un par tier)
            // Mais un vélo ne peut avoir qu'un seul tarif (pas de tier)
            $table->unique(['category_id', 'pricing_tier'], 'rates_category_tier_unique');
        });
    }

    public function down(): void
    {
        Schema::table('rates', function (Blueprint $table) {
            // Supprimer la contrainte composite
            $table->dropUnique('rates_category_tier_unique');

            // Supprimer la colonne pricing_tier
            $table->dropColumn('pricing_tier');

            // Recréer les contraintes UNIQUE d'origine
            $table->unique('category_id', 'rates_category_id_unique');
            $table->unique('bike_id', 'rates_bike_id_unique');
        });
    }
};
