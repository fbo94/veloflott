<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // Clé primaire UUID
            $table->uuid('id')->primary();

            // Identifiant Keycloak (claim "sub" du JWT)
            $table->string('keycloak_id')->unique();

            // Infos synchronisées depuis Keycloak
            $table->string('email')->unique();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();

            // Rôle géré côté Laravel (pas Keycloak)
            $table->enum('role', ['admin', 'manager', 'employee'])->default('employee');

            // Statut actif/inactif
            $table->boolean('is_active')->default(true);

            // Dernière connexion
            $table->timestamp('last_login_at')->nullable();

            // Timestamps
            $table->timestamps();

            // Index pour les filtres courants
            $table->index('role');
            $table->index('is_active');
            $table->index(['role', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
