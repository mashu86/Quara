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
        if (!Schema::hasColumn('categories', 'is_combo_offer')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->boolean('is_combo_offer')->default(false);
            });
        }
        if (!Schema::hasColumn('categories', 'min_count')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->integer('min_count')->nullable();
            });
        }
        if (!Schema::hasColumn('categories', 'combo_price')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->decimal('combo_price', 10, 2)->nullable();
            });
        }
        if (!Schema::hasColumn('categories', 'delivery_charge')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->decimal('delivery_charge', 10, 2)->default(0.00);
            });
        }

        if (!Schema::hasColumn('products', 'combo_category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('combo_category_id')->nullable()->constrained('categories')->nullOnDelete();
            });
        }
        if (!Schema::hasColumn('products', 'combo_sort_order')) {
            Schema::table('products', function (Blueprint $table) {
                $table->integer('combo_sort_order')->default(0);
            });
        }

        if (!Schema::hasColumn('orders', 'is_combo_offer')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->boolean('is_combo_offer')->default(false);
            });
        }
        if (!Schema::hasColumn('orders', 'combo_category_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreignId('combo_category_id')->nullable()->constrained('categories')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('order_items', 'is_combo_offer')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->boolean('is_combo_offer')->default(false);
            });
        }
        if (!Schema::hasColumn('order_items', 'combo_unit_price')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->decimal('combo_unit_price', 10, 2)->nullable();
            });
        }
        if (!Schema::hasColumn('order_items', 'original_price')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->decimal('original_price', 10, 2)->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'original_price')) {
                $table->dropColumn(['is_combo_offer', 'combo_unit_price', 'original_price']);
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'combo_category_id')) {
                $table->dropForeign(['combo_category_id']);
                $table->dropColumn(['is_combo_offer', 'combo_category_id']);
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'combo_category_id')) {
                $table->dropForeign(['combo_category_id']);
                $table->dropColumn(['combo_category_id', 'combo_sort_order']);
            }
        });
    }
};
