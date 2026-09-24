<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_carousel_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(false);
            $table->string('carousel_type')->default('hero');
            $table->string('animation')->default('slide');
            $table->unsignedSmallInteger('interval_ms')->default(5000);
            $table->unsignedSmallInteger('visible_count')->default(5);
            $table->timestamps();
        });

        Schema::create('home_carousel_slides', function (Blueprint $table) {
            $table->id();
            $table->string('heading')->nullable();
            $table->string('heading_color', 20)->default('#ffffff');
            $table->string('heading_font', 100)->default('Georgia, serif');
            $table->string('subheading')->nullable();
            $table->string('subheading_color', 20)->default('#ffffff');
            $table->string('image_mime', 100);
            $table->unsignedInteger('sort_order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('ALTER TABLE home_carousel_slides ADD COLUMN image_blob BLOB NULL');
        } else {
            DB::statement('ALTER TABLE home_carousel_slides ADD image_blob LONGBLOB NULL AFTER image_mime');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('home_carousel_slides');
        Schema::dropIfExists('home_carousel_settings');
    }
};
