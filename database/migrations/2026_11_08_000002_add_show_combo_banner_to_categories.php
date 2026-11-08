<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('categories', 'show_combo_banner')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->boolean('show_combo_banner')->default(true);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('categories', 'show_combo_banner')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('show_combo_banner');
            });
        }
    }
};
