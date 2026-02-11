<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Add organization-related fields to tenants table.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->string('address', 500)->nullable()->after('slug');
            $table->string('logo_url', 500)->nullable()->after('address');
            $table->string('subscription_plan', 50)->default('free')->after('status');
            $table->integer('max_users')->default(5)->after('subscription_plan');
            $table->integer('max_bikes')->default(50)->after('max_users');
            $table->integer('max_sites')->default(1)->after('max_bikes');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->dropColumn([
                'address',
                'logo_url',
                'subscription_plan',
                'max_users',
                'max_bikes',
                'max_sites',
            ]);
        });
    }
};
