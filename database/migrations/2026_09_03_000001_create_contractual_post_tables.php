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
        Schema::create('wallet_recharges', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->decimal('amount', 10, 2);
            $table->unsignedBigInteger('expense_id')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('expense_id')->references('id')->on('expenses')->onDelete('cascade');
        });

        Schema::create('contractual_couriers', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('courier_count');
            $table->decimal('total_amount', 10, 2);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contractual_couriers');
        Schema::dropIfExists('wallet_recharges');
    }
};
