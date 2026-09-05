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
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'item_status')) {
                $table->string('item_status')->default('active')->after('subtotal');
            }
            if (!Schema::hasColumn('order_items', 'inventory_condition')) {
                $table->string('inventory_condition')->nullable()->after('item_status');
            }
            if (!Schema::hasColumn('order_items', 'refund_amount')) {
                $table->decimal('refund_amount', 10, 2)->default(0.00)->after('inventory_condition');
            }
            if (!Schema::hasColumn('order_items', 'exchange_item_id')) {
                $table->unsignedBigInteger('exchange_item_id')->nullable()->after('refund_amount');
            }
        });

        Schema::table('order_operations', function (Blueprint $table) {
            if (!Schema::hasColumn('order_operations', 'inventory_condition')) {
                $table->string('inventory_condition')->default('return_to_stock')->after('is_product_restored');
            }
            if (!Schema::hasColumn('order_operations', 'replacement_product_id')) {
                $table->unsignedBigInteger('replacement_product_id')->nullable()->after('inventory_condition');
            }
            if (!Schema::hasColumn('order_operations', 'replacement_product_size_id')) {
                $table->unsignedBigInteger('replacement_product_size_id')->nullable()->after('replacement_product_id');
            }
            if (!Schema::hasColumn('order_operations', 'replacement_quantity')) {
                $table->integer('replacement_quantity')->nullable()->after('replacement_product_size_id');
            }
            if (!Schema::hasColumn('order_operations', 'price_difference')) {
                $table->decimal('price_difference', 10, 2)->default(0.00)->after('replacement_quantity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['item_status', 'inventory_condition', 'refund_amount', 'exchange_item_id']);
        });

        Schema::table('order_operations', function (Blueprint $table) {
            $table->dropColumn(['inventory_condition', 'replacement_product_id', 'replacement_product_size_id', 'replacement_quantity', 'price_difference']);
        });
    }
};
