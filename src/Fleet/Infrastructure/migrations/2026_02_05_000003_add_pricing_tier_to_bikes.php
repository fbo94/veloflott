<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('bikes', function (Blueprint $table) {
            // Ajouter la colonne pricing_tier avec valeur par défaut 'standard'
            $table->enum('pricing_tier', ['standard', 'premium'])
                ->default('standard')
                ->after('status')
                ->comment('Niveau de pricing du vélo (standard ou premium)');

            // Ajouter un index pour les recherches par tier
            $table->index('pricing_tier');
        });
    }

    public function down(): void
    {
        Schema::table('bikes', function (Blueprint $table) {
            $table->dropIndex(['pricing_tier']);
            $table->dropColumn('pricing_tier');
        });
    }
};
