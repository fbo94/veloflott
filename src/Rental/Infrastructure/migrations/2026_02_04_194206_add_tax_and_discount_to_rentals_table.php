<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            // Discount (réduction)
            $table->decimal('discount_amount', 10, 2)->default(0)->after('total_amount');

            // Tax (TVA)
            $table->decimal('tax_rate', 5, 2)->default(20.00)->after('discount_amount'); // Taux de TVA en %
            $table->decimal('tax_amount', 10, 2)->default(0)->after('tax_rate'); // Montant de TVA calculé

            // Total TTC
            $table->decimal('total_with_tax', 10, 2)->default(0)->after('tax_amount');
        });
    }

    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropColumn(['discount_amount', 'tax_rate', 'tax_amount', 'total_with_tax']);
        });
    }
};
