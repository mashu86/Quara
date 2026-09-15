<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'is_offer_category')) {
                $table->boolean('is_offer_category')->default(false)->after('sort_order');
            }
            if (!Schema::hasColumn('categories', 'offer_type')) {
                $table->string('offer_type', 20)->default('combo')->after('is_offer_category');
            }
            if (!Schema::hasColumn('categories', 'discount_value')) {
                $table->decimal('discount_value', 10, 2)->nullable()->after('combo_price');
            }
            if (!Schema::hasColumn('categories', 'discount_type')) {
                $table->string('discount_type', 20)->default('percentage')->after('discount_value');
            }
            if (!Schema::hasColumn('categories', 'is_active_offer')) {
                $table->boolean('is_active_offer')->default(false)->after('discount_type');
            }
        });

        // Data migration for existing combo offer categories
        if (Schema::hasColumn('categories', 'is_combo_offer')) {
            DB::table('categories')->where('is_combo_offer', true)->update([
                'is_offer_category' => true,
                'offer_type' => 'combo',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('categories', 'is_offer_category')) $cols[] = 'is_offer_category';
            if (Schema::hasColumn('categories', 'offer_type')) $cols[] = 'offer_type';
            if (Schema::hasColumn('categories', 'discount_value')) $cols[] = 'discount_value';
            if (Schema::hasColumn('categories', 'discount_type')) $cols[] = 'discount_type';
            if (Schema::hasColumn('categories', 'is_active_offer')) $cols[] = 'is_active_offer';
            
            if (count($cols) > 0) {
                $table->dropColumn($cols);
            }
        });
    }
};
