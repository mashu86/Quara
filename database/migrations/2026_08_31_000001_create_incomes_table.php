<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->string('income_name');
            $table->decimal('income_price', 10, 2);
            $table->enum('type', ['wholesale_selling', 'other'])->default('other');
            $table->integer('selling_pieces')->nullable()->default(1);
            $table->decimal('total_income_amount', 10, 2);
            $table->date('income_date');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
