<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('size'); // XS, S, M, L, XL, XXL, Free Size
            $table->integer('stock')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'size']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_sizes');
    }
};
