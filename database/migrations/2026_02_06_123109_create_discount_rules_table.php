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
        Schema::create('discount_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Applicabilité (nullable = toutes)
            $table->uuid('category_id')->nullable();      // null = toutes catégories
            $table->uuid('pricing_class_id')->nullable(); // null = toutes classes

            // Condition de déclenchement
            $table->integer('min_days')->nullable();              // Ex: 3 jours
            $table->uuid('min_duration_id')->nullable();          // Ex: "À partir de week-end"

            // Réduction
            $table->enum('discount_type', ['percentage', 'fixed']);  // percentage ou montant fixe
            $table->decimal('discount_value', 10, 2);                // 10 pour 10% ou 10€

            // UI
            $table->string('label');                      // "Réduction longue durée -10%"
            $table->text('description')->nullable();

            // Cumul
            $table->boolean('is_cumulative')->default(false);
            $table->integer('priority')->default(0);      // Ordre d'application

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('pricing_class_id')->references('id')->on('pricing_classes')->onDelete('cascade');
            $table->foreign('min_duration_id')->references('id')->on('duration_definitions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_rules');
    }
};
