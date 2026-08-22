<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // QW-20260821-00001
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->string('house_building');
            $table->string('street');
            $table->string('area');
            $table->string('city');
            $table->string('district');
            $table->string('state');
            $table->string('pin_code');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0.00);
            $table->decimal('shipping', 10, 2)->default(0.00);
            $table->decimal('grand_total', 10, 2);
            $table->enum('payment_method', ['cod', 'online'])->default('cod');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->enum('order_status', ['pending', 'confirmed', 'processing', 'packed', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('order_number');
            $table->index('order_status');
            $table->index('payment_status');
            $table->index('customer_phone');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
