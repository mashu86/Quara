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
        Schema::create('shipping_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('criteria_type', ['cart_count', 'cart_price'])->default('cart_price');
            $table->decimal('from_value', 10, 2)->default(0);
            $table->enum('from_operator', ['<', '<=', '>', '>='])->default('>=');
            $table->decimal('to_value', 10, 2)->nullable();
            $table->enum('to_operator', ['<', '<=', '>', '>='])->nullable();
            $table->enum('delivery_type', ['free', 'custom'])->default('free');
            $table->decimal('charge_amount', 10, 2)->default(0.00);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('priority')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_policies');
    }
};
