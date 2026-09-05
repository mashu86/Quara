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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'reserved_until')) {
                $table->timestamp('reserved_until')->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('orders', 'is_legacy_pending')) {
                $table->boolean('is_legacy_pending')->default(false)->after('reserved_until');
            }
        });

        // Mark all existing pending online orders as legacy pending technical issue records
        DB::table('orders')
            ->where('payment_method', 'online')
            ->where('payment_status', 'pending')
            ->update(['is_legacy_pending' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'reserved_until')) {
                $table->dropColumn('reserved_until');
            }
            if (Schema::hasColumn('orders', 'is_legacy_pending')) {
                $table->dropColumn('is_legacy_pending');
            }
        });
    }
};
