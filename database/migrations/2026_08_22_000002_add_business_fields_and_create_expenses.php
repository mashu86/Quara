<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add delivery charge type and weight to products
        Schema::table('products', function (Blueprint $table) {
            $table->enum('delivery_charge_type', ['include', 'exclude'])->default('exclude')->after('status');
            $table->decimal('weight_kg', 8, 2)->default(0.30)->after('delivery_charge_type');
        });

        // 2. Add courier dispatch tracking & order source to orders
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_dispatched_to_courier')->default(false)->after('is_cancellation_disabled');
            $table->string('courier_partner')->nullable()->after('is_dispatched_to_courier');
            $table->string('tracking_number')->nullable()->after('courier_partner');
            $table->timestamp('dispatched_at')->nullable()->after('tracking_number');
            $table->enum('order_source', ['online', 'manual'])->default('online')->after('dispatched_at');
        });

        // 3. Create expenses table
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->decimal('amount', 10, 2);
            $table->string('category')->default('General');
            $table->date('expense_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['is_dispatched_to_courier', 'courier_partner', 'tracking_number', 'dispatched_at', 'order_source']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['delivery_charge_type', 'weight_kg']);
        });
    }
};
