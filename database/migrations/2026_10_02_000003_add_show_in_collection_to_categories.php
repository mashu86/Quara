<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('categories', 'show_in_collection')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->boolean('show_in_collection')->default(true);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('categories', 'show_in_collection')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('show_in_collection');
            });
        }
    }
};
