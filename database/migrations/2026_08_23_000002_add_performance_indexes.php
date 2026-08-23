<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Products Indexes
        Schema::table('products', function (Blueprint $table) {
            $table->index(['status', 'category_id'], 'idx_products_status_category');
            $table->index(['status', 'id'], 'idx_products_status_id');
            $table->index(['category_id', 'status', 'final_price'], 'idx_products_cat_status_price');
        });

        // 2. Product Images Indexes
        Schema::table('product_images', function (Blueprint $table) {
            $table->index(['product_id', 'is_primary', 'sort_order'], 'idx_product_images_pid_primary_sort');
        });

        // 3. Product Sizes Indexes
        Schema::table('product_sizes', function (Blueprint $table) {
            $table->index(['product_id', 'stock'], 'idx_product_sizes_pid_stock');
            $table->index(['size', 'stock'], 'idx_product_sizes_size_stock');
        });

        // 4. Orders Indexes
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['customer_phone', 'id'], 'idx_orders_phone_id');
            $table->index(['order_status', 'created_at'], 'idx_orders_status_created');
            $table->index(['payment_status', 'created_at'], 'idx_orders_payment_created');
        });

        // 5. Home Contents Index
        Schema::table('home_contents', function (Blueprint $table) {
            $table->index(['status'], 'idx_home_contents_status');
        });

        // 6. Social Medias Index
        Schema::table('social_medias', function (Blueprint $table) {
            $table->index(['status', 'sort_order'], 'idx_social_medias_status_sort');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_status_category');
            $table->dropIndex('idx_products_status_id');
            $table->dropIndex('idx_products_cat_status_price');
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->dropIndex('idx_product_images_pid_primary_sort');
        });

        Schema::table('product_sizes', function (Blueprint $table) {
            $table->dropIndex('idx_product_sizes_pid_stock');
            $table->dropIndex('idx_product_sizes_size_stock');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_phone_id');
            $table->dropIndex('idx_orders_status_created');
            $table->dropIndex('idx_orders_payment_created');
        });

        Schema::table('home_contents', function (Blueprint $table) {
            $table->dropIndex('idx_home_contents_status');
        });

        Schema::table('social_medias', function (Blueprint $table) {
            $table->dropIndex('idx_social_medias_status_sort');
        });
    }
};
