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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('razorpay_fee_percent', 5, 2)->default(0.00)->after('payment_status');
            $table->decimal('razorpay_gst_percent', 5, 2)->default(0.00)->after('razorpay_fee_percent');
            $table->decimal('razorpay_base_fee', 10, 2)->default(0.00)->after('razorpay_gst_percent');
            $table->decimal('razorpay_gst_fee', 10, 2)->default(0.00)->after('razorpay_base_fee');
            $table->decimal('razorpay_total_charge', 10, 2)->default(0.00)->after('razorpay_gst_fee');
            $table->decimal('razorpay_net_amount', 10, 2)->default(0.00)->after('razorpay_total_charge');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('razorpay_fee_percent', 5, 2)->default(0.00)->after('status');
            $table->decimal('razorpay_gst_percent', 5, 2)->default(0.00)->after('razorpay_fee_percent');
            $table->decimal('razorpay_base_fee', 10, 2)->default(0.00)->after('razorpay_gst_percent');
            $table->decimal('razorpay_gst_fee', 10, 2)->default(0.00)->after('razorpay_base_fee');
            $table->decimal('razorpay_total_charge', 10, 2)->default(0.00)->after('razorpay_gst_fee');
            $table->decimal('razorpay_net_amount', 10, 2)->default(0.00)->after('razorpay_total_charge');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'razorpay_fee_percent',
                'razorpay_gst_percent',
                'razorpay_base_fee',
                'razorpay_gst_fee',
                'razorpay_total_charge',
                'razorpay_net_amount',
            ]);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'razorpay_fee_percent',
                'razorpay_gst_percent',
                'razorpay_base_fee',
                'razorpay_gst_fee',
                'razorpay_total_charge',
                'razorpay_net_amount',
            ]);
        });
    }
};
