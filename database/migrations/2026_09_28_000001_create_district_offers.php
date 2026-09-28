<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('district_offers', function (Blueprint $table) {
            $table->id();
            $table->string('district', 100);
            $table->string('state', 100)->default('Kerala');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('method', 20);
            $table->decimal('value', 12, 2);
            $table->boolean('is_active')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->index(['district', 'state', 'id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('district_offer_id')->nullable()->index();
            $table->json('district_offer_snapshot')->nullable();
            $table->decimal('district_offer_discount', 12, 2)->default(0);
            $table->boolean('district_offer_accepted')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['district_offer_id']);
            $table->dropColumn(['district_offer_id', 'district_offer_snapshot', 'district_offer_discount', 'district_offer_accepted']);
        });
        Schema::dropIfExists('district_offers');
    }
};
