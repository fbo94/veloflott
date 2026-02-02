<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_equipments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('rental_id');
            $table->string('type', 50); // Enum EquipmentType
            $table->integer('quantity');
            $table->decimal('price_per_unit', 10, 2);
            $table->timestamps();

            $table->foreign('rental_id')
                ->references('id')
                ->on('rentals')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_equipments');
    }
};
