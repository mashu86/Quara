<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Courier Excel rows that were not connected to an order; kept until an admin connects them by order ID.
        Schema::create('unmapped_courier_articles', function (Blueprint $table) {
            $table->id();
            $table->string('article_number')->unique();
            $table->string('tracking_number')->nullable();
            $table->string('receiver_name');
            $table->text('receiver_address');
            $table->string('pin_code', 20)->nullable();
            $table->decimal('courier_charge', 10, 2)->nullable();
            $table->string('status')->default('Not mapped from order')->index();
            $table->foreignId('order_shipment_id')->nullable()->constrained('order_shipments')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('connected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('connected_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unmapped_courier_articles');
    }
};
