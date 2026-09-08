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
        if (!Schema::hasColumn('product_sizes', 'reserved_stock')) {
            Schema::table('product_sizes', function (Blueprint $table) {
                $table->integer('reserved_stock')->default(0)->after('stock');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('product_sizes', 'reserved_stock')) {
            Schema::table('product_sizes', function (Blueprint $table) {
                $table->dropColumn('reserved_stock');
            });
        }
    }
};
