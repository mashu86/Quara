<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('master_coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('discount_type', ['fixed', 'percentage']);
            $table->decimal('discount_value', 10, 2);
            $table->decimal('minimum_purchase', 10, 2)->default(0);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('allow_with_offer')->default(false);
            $table->boolean('all_categories')->default(true);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
        Schema::create('master_coupon_category', function (Blueprint $table) {
            $table->foreignId('master_coupon_id')->constrained('master_coupons')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->primary(['master_coupon_id', 'category_id']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('master_coupon_id')->nullable()->after('district_offer_discount')->constrained('master_coupons')->nullOnDelete();
            $table->string('coupon_code')->nullable()->after('master_coupon_id');
            $table->decimal('coupon_discount', 10, 2)->default(0)->after('coupon_code');
            $table->boolean('coupon_usage_recorded')->default(false)->after('coupon_discount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('master_coupon_id');
            $table->dropColumn(['coupon_code', 'coupon_discount', 'coupon_usage_recorded']);
        });
        Schema::dropIfExists('master_coupon_category');
        Schema::dropIfExists('master_coupons');
    }
};
