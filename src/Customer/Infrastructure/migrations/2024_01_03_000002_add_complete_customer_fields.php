<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('identity_document_type', 50)->nullable()->after('phone');
            $table->string('identity_document_number', 100)->nullable()->after('identity_document_type');
            $table->integer('height')->nullable()->comment('Height in cm')->after('identity_document_number');
            $table->integer('weight')->nullable()->comment('Weight in kg')->after('height');
            $table->text('address')->nullable()->after('weight');
            $table->boolean('is_risky')->default(false)->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'identity_document_type',
                'identity_document_number',
                'height',
                'weight',
                'address',
                'is_risky',
            ]);
        });
    }
};
