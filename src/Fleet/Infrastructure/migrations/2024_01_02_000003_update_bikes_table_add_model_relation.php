<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bikes', function (Blueprint $table) {
            // Supprimer les anciennes colonnes brand et model
            $table->dropColumn(['brand', 'model']);

            // Ajouter la relation vers models
            $table->uuid('model_id')->after('internal_number');

            $table->foreign('model_id')
                ->references('id')
                ->on('models')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('bikes', function (Blueprint $table) {
            $table->dropForeign(['model_id']);
            $table->dropColumn('model_id');

            $table->string('brand', 100)->after('internal_number');
            $table->string('model', 100)->after('brand');
        });
    }
};
