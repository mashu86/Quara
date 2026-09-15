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
        if (!Schema::hasColumn('orders', 'rounding_adjustment')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->decimal('rounding_adjustment', 10, 2)->default(0.00)->after('shipping');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('orders', 'rounding_adjustment')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('rounding_adjustment');
            });
        }
    }
};
