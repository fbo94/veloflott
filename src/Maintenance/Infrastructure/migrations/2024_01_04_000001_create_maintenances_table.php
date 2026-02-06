<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('bike_id');
            $table->string('type'); // 'preventive' | 'curative'
            $table->string('reason'); // 'full_service' | 'brake_bleeding' | 'suspension' | 'wheels' | 'other'
            $table->string('priority'); // 'normal' | 'urgent'
            $table->string('status'); // 'todo' | 'in_progress' | 'completed'
            $table->text('description')->nullable();
            $table->timestamp('scheduled_at');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('work_description')->nullable();
            $table->text('parts_replaced')->nullable();
            $table->integer('cost')->nullable()->comment('Cost in cents');
            $table->timestamps();

            // Index
            $table->index('bike_id');
            $table->index('status');
            $table->index(['priority', 'status']);
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};
