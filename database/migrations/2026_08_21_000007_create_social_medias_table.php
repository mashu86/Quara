<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_medias', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['whatsapp', 'instagram', 'facebook', 'youtube']);
            $table->string('country_code')->default('+91');
            $table->string('phone_number')->nullable();
            $table->string('url')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_medias');
    }
};
