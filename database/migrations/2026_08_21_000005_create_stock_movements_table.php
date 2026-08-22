<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_size_id')->nullable()->constrained('product_sizes')->onDelete('set null');
            $table->string('size')->nullable();
            $table->integer('previous_stock');
            $table->integer('new_stock');
            $table->integer('difference');
            $table->string('reason')->nullable();
            $table->string('admin_name')->nullable();
            $table->timestamps();

            $table->index('product_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
