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
        Schema::create('pricing_classes', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('code')->unique();          // 'standard', 'premium', 'luxe', 'vip'
            $table->string('label');                   // "Standard", "Premium", "Luxe"
            $table->text('description')->nullable();
            $table->string('color', 7)->nullable();    // #3B82F6 (pour UI)

            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_classes');
    }
};
