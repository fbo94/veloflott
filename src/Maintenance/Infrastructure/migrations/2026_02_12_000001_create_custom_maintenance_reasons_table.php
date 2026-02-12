<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('custom_maintenance_reasons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('code', 100)->unique();
            $table->string('label');
            $table->text('description')->nullable();
            $table->string('category'); // Référence à MaintenanceCategory
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            $table->index('tenant_id');
            $table->index('category');
            $table->index('is_active');
            $table->index(['tenant_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_maintenance_reasons');
    }
};
