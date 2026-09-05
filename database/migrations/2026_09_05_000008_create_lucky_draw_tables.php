<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lucky_draws', function (Blueprint $table) {
            $table->id();
            $table->string('draw_number')->nullable()->unique();
            $table->uuid('draft_token')->unique();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('admin_name');
            $table->string('draw_type', 20);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('selected_month', 7)->nullable();
            $table->string('period_label');
            $table->string('timezone', 50);
            $table->unsignedInteger('total_successful_orders');
            $table->unsignedInteger('total_entries');
            $table->unsignedInteger('gift_count');
            $table->unsignedInteger('winner_count');
            $table->json('selection_rules');
            $table->dateTime('eligibility_checked_at');
            $table->dateTime('drawn_at');
            $table->timestamps();
        });

        Schema::create('lucky_draw_winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lucky_draw_id')->constrained('lucky_draws')->cascadeOnDelete();
            $table->unsignedInteger('position');
            // Snapshots preserve history even if the source order is later deleted.
            $table->unsignedBigInteger('order_id');
            $table->string('order_number');
            $table->string('customer_name');
            $table->dateTime('order_date');
            $table->text('customer_address');
            $table->string('order_type', 20);
            $table->json('eligibility');
            $table->dateTime('selected_at');
            $table->timestamps();
            $table->unique(['lucky_draw_id', 'position']);
            $table->unique(['lucky_draw_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lucky_draw_winners');
        Schema::dropIfExists('lucky_draws');
    }
};
