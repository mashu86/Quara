<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('article_number')->nullable()->unique();
            $table->string('tracking_number')->nullable()->unique();
            $table->string('courier_name')->nullable();
            $table->string('receiver_name');
            $table->text('receiver_address');
            $table->decimal('weight', 10, 2)->nullable();
            $table->string('weight_unit', 20)->default('gram');
            $table->decimal('courier_charge', 10, 2)->nullable();
            $table->date('shipment_date')->nullable()->index();
            $table->string('shipment_status')->default('Generated');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('order_shipments'); }
};
