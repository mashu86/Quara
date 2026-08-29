<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            
            $table->string('operation_type')->default('product_returned');
            $table->string('other_description')->nullable();
            $table->string('status')->default('active'); // active, inactive
            $table->unsignedInteger('quantity')->default(1);
            $table->boolean('is_product_restored')->default(false);
            $table->boolean('is_money_refunded')->default(false);
            
            $table->decimal('product_refund_amount', 10, 2)->default(0.00);
            $table->decimal('delivery_refund_amount', 10, 2)->default(0.00);
            $table->decimal('other_refund_amount', 10, 2)->default(0.00);
            $table->decimal('total_refund_amount', 10, 2)->default(0.00);
            
            $table->decimal('additional_expense_total', 10, 2)->default(0.00);
            $table->decimal('total_financial_adjustment', 10, 2)->default(0.00);
            
            $table->text('notes')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('order_operation_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_operation_id')->constrained('order_operations')->onDelete('cascade');
            $table->string('description');
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_operation_expenses');
        Schema::dropIfExists('order_operations');
    }
};
