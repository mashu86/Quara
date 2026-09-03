<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_image_embeddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_image_id')->constrained()->cascadeOnDelete();
            $table->longText('embedding');
            $table->text('color_histogram')->nullable();
            $table->text('edge_histogram')->nullable();
            $table->string('checksum')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'product_image_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_image_embeddings');
    }
};
