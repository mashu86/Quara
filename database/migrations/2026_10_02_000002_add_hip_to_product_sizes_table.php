<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'measurement_type')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('measurement_type', 10)->default('up');
            });
        }
        if (! Schema::hasColumn('product_sizes', 'hip')) {
            Schema::table('product_sizes', function (Blueprint $table) {
                $table->string('hip', 50)->nullable()->after('waist');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('product_sizes', 'hip')) {
            Schema::table('product_sizes', function (Blueprint $table) {
                $table->dropColumn('hip');
            });
        }
        if (Schema::hasColumn('products', 'measurement_type')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('measurement_type');
            });
        }
    }
};
