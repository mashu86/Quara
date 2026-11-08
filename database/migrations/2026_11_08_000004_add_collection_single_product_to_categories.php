<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('categories', 'collection_single_product')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->boolean('collection_single_product')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('categories', 'collection_single_product')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('collection_single_product');
            });
        }
    }
};
