<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add trial and onboarding fields to tenants table.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->timestamp('trial_ends_at')->nullable()->after('subscription_plan_id');
            $table->boolean('onboarding_completed')->default(false)->after('trial_ends_at');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->dropColumn(['trial_ends_at', 'onboarding_completed']);
        });
    }
};
