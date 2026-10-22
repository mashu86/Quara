<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_size_id')->constrained('product_sizes')->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('sold_quantity')->default(0);
            $table->string('booked_by')->nullable();
            $table->string('booking_type')->default('whatsapp_customer');
            $table->date('booking_date');
            $table->string('status')->default('active'); // active, partially_sold, sold, released
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['product_size_id', 'status']);
            $table->index(['product_id', 'status']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('product_booking_id')->nullable()->after('product_size_id')->constrained('product_bookings')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_booking_id');
        });
        Schema::dropIfExists('product_bookings');
    }
};
