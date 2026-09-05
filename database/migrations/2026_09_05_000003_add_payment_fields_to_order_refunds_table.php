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
        Schema::table('order_refunds', function (Blueprint $table) {
            if (!Schema::hasColumn('order_refunds', 'payment_method')) {
                $table->string('payment_method')->default('manual')->after('refund_date');
            }
            if (!Schema::hasColumn('order_refunds', 'transaction_reference')) {
                $table->string('transaction_reference')->nullable()->after('payment_method');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_refunds', function (Blueprint $table) {
            if (Schema::hasColumn('order_refunds', 'transaction_reference')) {
                $table->dropColumn('transaction_reference');
            }
            if (Schema::hasColumn('order_refunds', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
        });
    }
};
