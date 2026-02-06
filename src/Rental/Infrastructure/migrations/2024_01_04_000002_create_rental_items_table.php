<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('rental_id');
            $table->uuid('bike_id');
            $table->decimal('daily_rate', 10, 2);
            $table->integer('quantity')->default(1);

            // Check-in data (US 2.2)
            $table->integer('client_height')->nullable(); // en cm
            $table->integer('client_weight')->nullable(); // en kg
            $table->integer('saddle_height')->nullable(); // en cm
            $table->integer('front_suspension_pressure')->nullable(); // PSI
            $table->integer('rear_suspension_pressure')->nullable(); // PSI
            $table->string('pedal_type', 100)->nullable();
            $table->text('check_in_notes')->nullable();

            // Check-out data (US 2.3)
            $table->string('return_condition', 50)->nullable(); // Enum BikeCondition
            $table->text('damage_description')->nullable();
            $table->json('damage_photos')->nullable();

            $table->timestamps();

            $table->foreign('rental_id')
                ->references('id')
                ->on('rentals')
                ->onDelete('cascade');

            $table->foreign('bike_id')
                ->references('id')
                ->on('bikes')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_items');
    }
};
