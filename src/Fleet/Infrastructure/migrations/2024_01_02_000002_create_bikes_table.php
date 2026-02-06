<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('bikes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('qr_code_uuid')->unique();

            // Informations obligatoires
            $table->string('internal_number')->unique();
            $table->string('brand');
            $table->string('model');
            $table->uuid('category_id');

            // Taille du cadre
            $table->enum('frame_size_unit', ['letter', 'cm', 'inch']);
            $table->decimal('frame_size_numeric', 5, 2)->nullable();
            $table->enum('frame_size_letter', ['xs', 's', 'm', 'l', 'xl', 'xxl'])->nullable();
            $table->enum('frame_size_letter_equivalent', ['xs', 's', 'm', 'l', 'xl', 'xxl']);

            // Statut
            $table->enum('status', ['available', 'rented', 'maintenance', 'unavailable', 'retired'])
                ->default('available');

            // Informations optionnelles
            $table->integer('year')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('color')->nullable();
            $table->enum('wheel_size', ['27.5', '29', '700c'])->nullable();
            $table->integer('front_suspension')->nullable()->comment('mm');
            $table->integer('rear_suspension')->nullable()->comment('mm');
            $table->enum('brake_type', ['hydraulic_disc', 'mechanical_disc'])->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->text('notes')->nullable();

            // Photos (JSON array)
            $table->json('photos')->nullable();

            // Retirement
            $table->enum('retirement_reason', ['sold', 'stolen', 'permanently_out_of_service', 'other'])->nullable();
            $table->text('retirement_comment')->nullable();
            $table->timestamp('retired_at')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('restrict');

            // Index
            $table->index('status');
            $table->index('category_id');
            $table->index('frame_size_letter_equivalent');
            $table->index(['status', 'category_id']);
            $table->index('brand');
            $table->index('internal_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bikes');
    }
};
