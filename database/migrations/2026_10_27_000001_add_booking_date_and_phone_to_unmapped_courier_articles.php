<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unmapped_courier_articles', function (Blueprint $table) {
            // Kept as the courier file wrote them; formats differ between courier exports.
            $table->string('booking_date', 50)->nullable()->after('pin_code');
            $table->string('receiver_phone', 30)->nullable()->after('booking_date');
        });
    }

    public function down(): void
    {
        Schema::table('unmapped_courier_articles', function (Blueprint $table) {
            $table->dropColumn(['booking_date', 'receiver_phone']);
        });
    }
};
