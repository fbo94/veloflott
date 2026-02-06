<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->index('display_order');
            $table->index('is_default');
        });

        // Seed des catégories par défaut
        $defaultCategories = [
            ['name' => 'Enduro', 'order' => 1],
            ['name' => 'DH', 'order' => 2],
            ['name' => 'Route', 'order' => 3],
            ['name' => 'Gravel', 'order' => 4],
            ['name' => 'VTTAE', 'order' => 5],
        ];

        foreach ($defaultCategories as $category) {
            DB::table('categories')->insert([
                'id' => Str::uuid()->toString(),
                'name' => $category['name'],
                'description' => null,
                'is_default' => true,
                'display_order' => $category['order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
