<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('name');
            $table->string('slug');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 2)->default('FR');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->enum('status', ['active', 'suspended', 'closed'])->default('active');
            $table->json('opening_hours')->nullable();
            $table->json('settings')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();

            // Foreign key vers tenants
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            // Slug unique par tenant
            $table->unique(['tenant_id', 'slug'], 'sites_tenant_slug_unique');

            // Index pour les recherches
            $table->index('status');
            $table->index(['tenant_id', 'status']);
            $table->index('city');

            // Index spatial pour recherche géographique (approximatif)
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
