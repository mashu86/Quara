<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('payment_method')->default('cod'); // cod or online
            $table->string('transaction_id')->nullable();
            $table->string('razorpay_order_id')->nullable();
            $table->string('razorpay_payment_id')->nullable();
            $table->string('razorpay_signature')->nullable();
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded', 'cancelled'])->default('pending');
            $table->decimal('amount', 10, 2);
            $table->json('response_payload')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('razorpay_order_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
