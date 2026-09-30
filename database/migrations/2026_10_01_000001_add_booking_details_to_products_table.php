<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'booking_type')) {
                $table->string('booking_type', 40)->nullable()->after('booked_by');
            }

            if (! Schema::hasColumn('products', 'booking_date')) {
                $table->date('booking_date')->nullable()->after('booking_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'booking_date')) {
                $table->dropColumn('booking_date');
            }

            if (Schema::hasColumn('products', 'booking_type')) {
                $table->dropColumn('booking_type');
            }
        });
    }
};
