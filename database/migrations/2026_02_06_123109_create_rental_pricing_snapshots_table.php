<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rental_pricing_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('rental_id')->unique();

            // Prix calculé (immuable)
            $table->decimal('base_price', 10, 2);         // Prix de base (tarif × jours)
            $table->decimal('final_price', 10, 2);        // Prix final après réductions
            $table->json('discounts_applied')->nullable(); // Détail des réductions

            // Traçabilité des sources
            $table->uuid('category_id');
            $table->uuid('pricing_class_id');
            $table->uuid('duration_id');
            $table->integer('days');
            $table->decimal('price_per_day', 10, 2);

            // Métadonnées
            $table->timestamp('calculated_at');

            $table->timestamps();

            // Indexes
            $table->foreign('rental_id')->references('id')->on('rentals')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_pricing_snapshots');
    }
};
