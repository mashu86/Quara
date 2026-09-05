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
        if (!Schema::hasTable('salary_payments')) {
            Schema::create('salary_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->foreignId('salary_id')->nullable()->constrained('salaries')->nullOnDelete();
                $table->decimal('amount', 10, 2)->default(0.00);
                $table->date('payment_date');
                $table->string('payment_method')->default('cash');
                $table->string('transaction_type')->default('initial_payment'); // initial_payment, settlement
                $table->foreignId('expense_id')->nullable()->constrained('expenses')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->string('created_by')->nullable();
                $table->timestamps();

                $table->index('employee_id');
                $table->index('payment_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_payments');
    }
};
