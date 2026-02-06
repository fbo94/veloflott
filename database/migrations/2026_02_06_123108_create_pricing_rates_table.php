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
        Schema::create('pricing_rates', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Les 3 dimensions
            $table->uuid('category_id');           // FK vers categories
            $table->uuid('pricing_class_id');      // FK vers pricing_classes
            $table->uuid('duration_id');           // FK vers duration_definitions

            // Le prix
            $table->decimal('price', 10, 2);       // En euros (ex: 35.00)

            // Métadonnées
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Indexes & Contraintes
            $table->unique(['category_id', 'pricing_class_id', 'duration_id'], 'unique_pricing_rate');

            $table->foreign('category_id')->references('id')->on('categories')->onDelete('restrict');
            $table->foreign('pricing_class_id')->references('id')->on('pricing_classes')->onDelete('restrict');
            $table->foreign('duration_id')->references('id')->on('duration_definitions')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_rates');
    }
};
