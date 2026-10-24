<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_code', 50)->nullable()->unique()->after('name');
        });

        // Give every existing product a code in the order it was added: PR1, PR2, ...
        $next = 1;
        DB::table('products')->orderBy('id')->select('id')->chunkById(500, function ($products) use (&$next) {
            foreach ($products as $product) {
                DB::table('products')->where('id', $product->id)->update(['product_code' => 'PR' . $next++]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['product_code']);
            $table->dropColumn('product_code');
        });
    }
};
