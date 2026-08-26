<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['category_id', 'product_id']);
        });

        // Migrate existing product category relations
        $existingProducts = DB::table('products')->whereNotNull('category_id')->get(['id', 'category_id']);
        $now = now();
        $records = [];

        foreach ($existingProducts as $prod) {
            $records[] = [
                'category_id' => $prod->category_id,
                'product_id' => $prod->id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($records)) {
            DB::table('category_product')->insertOrIgnore($records);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('category_product');
    }
};
