<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create subscription_plans table.
     */
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name', 50)->unique(); // free, starter, professional, enterprise
            $table->string('display_name', 100); // Plan Gratuit, Plan Starter
            $table->text('description')->nullable();
            $table->decimal('price_monthly', 10, 2)->nullable(); // Prix mensuel en euros
            $table->decimal('price_yearly', 10, 2)->nullable(); // Prix annuel en euros
            $table->integer('max_users'); // Limite utilisateurs
            $table->integer('max_bikes'); // Limite vélos
            $table->integer('max_sites'); // Limite sites
            $table->json('features')->nullable(); // Fonctionnalités spécifiques
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
