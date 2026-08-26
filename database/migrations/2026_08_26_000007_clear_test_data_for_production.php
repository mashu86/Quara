<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('order_items')) DB::table('order_items')->truncate();
        if (Schema::hasTable('payments')) DB::table('payments')->truncate();
        if (Schema::hasTable('orders')) DB::table('orders')->truncate();
        if (Schema::hasTable('expenses')) DB::table('expenses')->truncate();
        if (Schema::hasTable('notifications')) DB::table('notifications')->truncate();
        if (Schema::hasTable('stock_movements')) DB::table('stock_movements')->truncate();
        if (Schema::hasTable('product_sizes')) DB::table('product_sizes')->truncate();
        if (Schema::hasTable('product_images')) DB::table('product_images')->truncate();
        if (Schema::hasTable('category_product')) DB::table('category_product')->truncate();
        if (Schema::hasTable('products')) DB::table('products')->truncate();
        if (Schema::hasTable('categories')) DB::table('categories')->truncate();

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Truncate migration is one-way
    }
};
