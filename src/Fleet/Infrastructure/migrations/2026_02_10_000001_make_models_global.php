<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make models table global by allowing NULL tenant_id.
     * Models can now be shared across all tenants (global catalogue) or specific to a tenant.
     */
    public function up(): void
    {
        Schema::table('models', function (Blueprint $table): void {
            // Make tenant_id nullable
            $table->uuid('tenant_id')->nullable()->change();

            // Update foreign key constraint to set NULL on tenant deletion
            if (Schema::hasColumn('models', 'tenant_id')) {
                $table->dropForeign(['tenant_id']);
                $table->foreign('tenant_id')
                    ->references('id')
                    ->on('tenants')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('models', function (Blueprint $table): void {
            // Restore NOT NULL constraint (requires all NULL values to be fixed first)
            $table->uuid('tenant_id')->nullable(false)->change();

            // Restore original foreign key constraint
            if (Schema::hasColumn('models', 'tenant_id')) {
                $table->dropForeign(['tenant_id']);
                $table->foreign('tenant_id')
                    ->references('id')
                    ->on('tenants')
                    ->onDelete('cascade');
            }
        });
    }
};
