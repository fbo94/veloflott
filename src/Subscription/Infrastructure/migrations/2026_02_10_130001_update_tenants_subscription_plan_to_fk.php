<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replace subscription_plan (string) with subscription_plan_id (FK).
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            // Supprimer l'ancienne colonne string
            $table->dropColumn('subscription_plan');

            // Ajouter la nouvelle colonne FK
            $table->uuid('subscription_plan_id')->nullable()->after('logo_url');
            $table->foreign('subscription_plan_id')
                ->references('id')
                ->on('subscription_plans')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->dropForeign(['subscription_plan_id']);
            $table->dropColumn('subscription_plan_id');
            $table->string('subscription_plan', 50)->default('free')->after('logo_url');
        });
    }
};
