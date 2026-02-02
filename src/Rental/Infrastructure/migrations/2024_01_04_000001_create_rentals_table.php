<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('customer_id');
            $table->timestamp('start_date');
            $table->timestamp('expected_return_date');
            $table->timestamp('actual_return_date')->nullable();
            $table->string('duration', 50); // Enum RentalDuration
            $table->decimal('deposit_amount', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->string('status', 50)->default('pending'); // Enum RentalStatus
            $table->string('deposit_status', 50)->default('held'); // Enum DepositStatus
            $table->decimal('deposit_retained', 10, 2)->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('restrict');

            $table->index(['status', 'start_date']);
            $table->index(['customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
