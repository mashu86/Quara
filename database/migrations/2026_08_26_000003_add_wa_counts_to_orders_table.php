<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('wa_thank_you_count')->default(0)->after('notes');
            $table->unsignedInteger('wa_couriered_count')->default(0)->after('wa_thank_you_count');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['wa_thank_you_count', 'wa_couriered_count']);
        });
    }
};
