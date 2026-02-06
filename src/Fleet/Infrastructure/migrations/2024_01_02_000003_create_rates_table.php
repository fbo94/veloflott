<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rates', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Un tarif est associé SOIT à une catégorie, SOIT à un vélo
            $table->uuid('category_id')->nullable();
            $table->uuid('bike_id')->nullable();

            // Tarifs par durée
            $table->decimal('half_day_price', 10, 2)->nullable();
            $table->decimal('day_price', 10, 2);
            $table->decimal('weekend_price', 10, 2)->nullable();
            $table->decimal('week_price', 10, 2)->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('cascade');

            $table->foreign('bike_id')
                ->references('id')
                ->on('bikes')
                ->onDelete('cascade');

            // Index et contraintes
            $table->unique('category_id');
            $table->unique('bike_id');
            $table->index('category_id');
            $table->index('bike_id');

            // Contrainte : category_id XOR bike_id
            // Géré au niveau application car pas supporté nativement par toutes les BDD
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rates');
    }
};
