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
        Schema::table('order_operations', function (Blueprint $table) {
            if (!Schema::hasColumn('order_operations', 'return_date')) {
                $table->date('return_date')->nullable()->after('status');
            }
            if (!Schema::hasColumn('order_operations', 'refund_date')) {
                $table->date('refund_date')->nullable()->after('return_date');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'return_date')) {
                $table->date('return_date')->nullable()->after('inventory_condition');
            }
            if (!Schema::hasColumn('order_items', 'refund_date')) {
                $table->date('refund_date')->nullable()->after('return_date');
            }
        });

        if (!Schema::hasTable('order_refunds')) {
            Schema::create('order_refunds', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
                $table->foreignId('order_operation_id')->nullable()->constrained('order_operations')->onDelete('cascade');
                $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
                $table->decimal('refund_amount', 10, 2)->default(0.00);
                $table->date('refund_date');
                $table->string('payment_method')->default('manual');
                $table->string('transaction_reference')->nullable();
                $table->text('notes')->nullable();
                $table->string('created_by')->nullable();
                $table->timestamps();

                $table->index('order_id');
                $table->index('refund_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_refunds');

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['return_date', 'refund_date']);
        });

        Schema::table('order_operations', function (Blueprint $table) {
            $table->dropColumn(['return_date', 'refund_date']);
        });
    }
};
