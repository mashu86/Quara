<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'allow_pre_min_purchase')) {
                $table->boolean('allow_pre_min_purchase')->default(false);
            }
            if (!Schema::hasColumn('categories', 'pre_min_purchase_offer_price')) {
                $table->boolean('pre_min_purchase_offer_price')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('categories', 'allow_pre_min_purchase')) $columns[] = 'allow_pre_min_purchase';
            if (Schema::hasColumn('categories', 'pre_min_purchase_offer_price')) $columns[] = 'pre_min_purchase_offer_price';
            if ($columns) $table->dropColumn($columns);
        });
    }
};
