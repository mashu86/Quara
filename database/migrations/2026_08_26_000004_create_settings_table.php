<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->timestamps();
        });

        // Insert default Razorpay Fee and GST percentage settings
        DB::table('settings')->insert([
            [
                'key' => 'razorpay_fee_percent',
                'value' => '2.00',
                'group' => 'payment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'razorpay_gst_percent',
                'value' => '18.00',
                'group' => 'payment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
