<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('new_order');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('cascade');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index('is_read');
            $table->index('order_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
