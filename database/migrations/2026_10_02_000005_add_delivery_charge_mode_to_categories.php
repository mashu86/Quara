<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('categories', 'delivery_charge_mode')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->enum('delivery_charge_mode', ['free', 'custom', 'master'])
                    ->default('free')
                    ->after('delivery_charge');
            });
        }

        DB::table('categories')
            ->where('delivery_charge', '>', 0)
            ->where('delivery_charge_mode', 'free')
            ->update(['delivery_charge_mode' => 'custom']);
    }

    public function down(): void
    {
        if (Schema::hasColumn('categories', 'delivery_charge_mode')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('delivery_charge_mode');
            });
        }
    }
};
