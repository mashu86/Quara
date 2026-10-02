<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'minimum_purchase_required')) {
                $table->boolean('minimum_purchase_required')->default(false);
            }
            if (!Schema::hasColumn('categories', 'minimum_purchase_count')) {
                $table->unsignedInteger('minimum_purchase_count')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('categories', 'minimum_purchase_required')) $columns[] = 'minimum_purchase_required';
            if (Schema::hasColumn('categories', 'minimum_purchase_count')) $columns[] = 'minimum_purchase_count';
            if ($columns) $table->dropColumn($columns);
        });
    }
};
