<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Scope (hiérarchie: app → tenant → site)
            // null + null = app default
            // tenant_id + null = tenant default
            // tenant_id + site_id = site specific
            $table->uuid('tenant_id')->nullable();
            $table->uuid('site_id')->nullable();

            // Configuration retard
            $table->integer('late_tolerance_minutes')->default(30);
            $table->decimal('hourly_late_rate', 10, 2)->default(10.00);
            $table->decimal('daily_late_rate', 10, 2)->default(50.00);

            // Configuration restitution anticipée
            $table->boolean('early_return_enabled')->default(true);
            $table->enum('early_return_fee_type', ['percentage', 'fixed', 'none'])->default('percentage');
            $table->decimal('early_return_fee_percentage', 5, 2)->nullable()->default(30.00);
            $table->decimal('early_return_fee_fixed', 10, 2)->nullable()->default(25.00);

            // Autres paramètres
            $table->integer('max_rental_duration_days')->default(30);
            $table->integer('min_reservation_hours_ahead')->default(2);

            $table->timestamps();

            // Contrainte d'unicité sur le scope
            $table->unique(['tenant_id', 'site_id'], 'rental_settings_scope_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_settings');
    }
};
