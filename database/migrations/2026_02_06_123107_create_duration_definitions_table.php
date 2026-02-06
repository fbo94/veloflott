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
        Schema::create('duration_definitions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('code')->unique();          // 'half_day', 'full_day', 'weekend'
            $table->string('label');                   // "Demi-journée", "Journée"

            $table->integer('duration_hours')->nullable();  // 4 pour demi-journée
            $table->integer('duration_days')->nullable();   // 1 pour journée, 7 pour semaine
            $table->boolean('is_custom')->default(false);   // true = "Durée personnalisée"

            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('duration_definitions');
    }
};
