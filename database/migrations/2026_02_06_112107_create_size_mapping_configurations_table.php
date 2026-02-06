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
        Schema::create('size_mapping_configurations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('version')->unsigned();
            $table->boolean('is_active')->default(false);

            // XS ranges
            $table->integer('xs_cm_min')->unsigned();
            $table->integer('xs_cm_max')->unsigned();
            $table->integer('xs_inch_min')->unsigned();
            $table->integer('xs_inch_max')->unsigned();

            // S ranges
            $table->integer('s_cm_min')->unsigned();
            $table->integer('s_cm_max')->unsigned();
            $table->integer('s_inch_min')->unsigned();
            $table->integer('s_inch_max')->unsigned();

            // M ranges
            $table->integer('m_cm_min')->unsigned();
            $table->integer('m_cm_max')->unsigned();
            $table->integer('m_inch_min')->unsigned();
            $table->integer('m_inch_max')->unsigned();

            // L ranges
            $table->integer('l_cm_min')->unsigned();
            $table->integer('l_cm_max')->unsigned();
            $table->integer('l_inch_min')->unsigned();
            $table->integer('l_inch_max')->unsigned();

            // XL ranges
            $table->integer('xl_cm_min')->unsigned();
            $table->integer('xl_cm_max')->unsigned();
            $table->integer('xl_inch_min')->unsigned();
            $table->integer('xl_inch_max')->unsigned();

            // XXL ranges
            $table->integer('xxl_cm_min')->unsigned();
            $table->integer('xxl_cm_max')->unsigned();
            $table->integer('xxl_inch_min')->unsigned();
            $table->integer('xxl_inch_max')->unsigned();

            $table->timestamps();

            // Indexes
            $table->index('version');
            $table->index('is_active');
            $table->unique(['version', 'is_active'], 'unique_active_version');
        });

        // Insert default configuration
        DB::table('size_mapping_configurations')->insert([
            'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'version' => 1,
            'is_active' => true,
            'xs_cm_min' => 48,
            'xs_cm_max' => 50,
            'xs_inch_min' => 13,
            'xs_inch_max' => 14,
            's_cm_min' => 51,
            's_cm_max' => 53,
            's_inch_min' => 15,
            's_inch_max' => 16,
            'm_cm_min' => 54,
            'm_cm_max' => 56,
            'm_inch_min' => 17,
            'm_inch_max' => 18,
            'l_cm_min' => 57,
            'l_cm_max' => 59,
            'l_inch_min' => 19,
            'l_inch_max' => 20,
            'xl_cm_min' => 60,
            'xl_cm_max' => 62,
            'xl_inch_min' => 21,
            'xl_inch_max' => 22,
            'xxl_cm_min' => 63,
            'xxl_cm_max' => 999,
            'xxl_inch_min' => 23,
            'xxl_inch_max' => 999,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('size_mapping_configurations');
    }
};
