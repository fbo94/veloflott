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
        Schema::create('bike_status_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('bike_id');
            $table->string('old_status');
            $table->string('new_status');
            $table->string('unavailability_reason')->nullable();
            $table->text('unavailability_comment')->nullable();
            $table->timestamp('changed_at');

            $table->foreign('bike_id')->references('id')->on('bikes')->onDelete('cascade');
            $table->index('bike_id');
            $table->index('changed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bike_status_history');
    }
};
