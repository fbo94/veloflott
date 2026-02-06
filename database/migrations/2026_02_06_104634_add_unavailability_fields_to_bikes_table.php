<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bikes', function (Blueprint $table) {
            $table->string('unavailability_reason')->nullable()->after('status');
            $table->text('unavailability_comment')->nullable()->after('unavailability_reason');
        });

        // Add CHECK constraint for PostgreSQL only (SQLite doesn't support ALTER TABLE ADD CONSTRAINT for CHECK)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE bikes ADD CONSTRAINT bikes_unavailability_reason_check CHECK (unavailability_reason IN ('reserved', 'loaned', 'other') OR unavailability_reason IS NULL)");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop CHECK constraint for PostgreSQL only
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE bikes DROP CONSTRAINT IF EXISTS bikes_unavailability_reason_check");
        }

        Schema::table('bikes', function (Blueprint $table) {
            $table->dropColumn(['unavailability_reason', 'unavailability_comment']);
        });
    }
};
