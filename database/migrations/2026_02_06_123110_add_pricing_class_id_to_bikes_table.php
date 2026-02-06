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
        Schema::table('bikes', function (Blueprint $table) {
            $table->uuid('pricing_class_id')->nullable()->after('category_id');

            $table->foreign('pricing_class_id')
                  ->references('id')
                  ->on('pricing_classes')
                  ->onDelete('restrict');

            $table->index('pricing_class_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bikes', function (Blueprint $table) {
            $table->dropForeign(['pricing_class_id']);
            $table->dropIndex(['pricing_class_id']);
            $table->dropColumn('pricing_class_id');
        });
    }
};
