<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposit_retention_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Cible (une seule doit être remplie) - Hiérarchie: bike > pricing_class > category
            $table->uuid('bike_id')->nullable();
            $table->uuid('pricing_class_id')->nullable();
            $table->uuid('category_id')->nullable();

            // Montants de retenue selon la gravité des dégâts
            $table->decimal('minor_damage_amount', 10, 2)->default(50.00);
            $table->decimal('major_damage_amount', 10, 2)->default(200.00);
            $table->decimal('total_loss_amount', 10, 2)->default(500.00);

            $table->timestamps();

            // Foreign keys
            $table->foreign('bike_id')
                ->references('id')
                ->on('bikes')
                ->onDelete('cascade');

            $table->foreign('pricing_class_id')
                ->references('id')
                ->on('pricing_classes')
                ->onDelete('cascade');

            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('cascade');

            // Index pour recherche rapide
            $table->index('bike_id');
            $table->index('pricing_class_id');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposit_retention_configs');
    }
};
