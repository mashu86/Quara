<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_carousel_slides', function (Blueprint $table) {
            $table->string('section_key', 30)->default('hero')->after('id')->index();
        });

        Schema::create('home_page_sections', function (Blueprint $table) {
            $table->id();
            $table->string('section_key', 30)->unique();
            $table->boolean('enabled')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unsignedSmallInteger('items_to_show')->default(8);
            $table->timestamps();
        });

        foreach (['hero', 'categories', 'new_arrivals', 'best_sellers', 'offers', 'lookbook', 'reviews'] as $index => $key) {
            DB::table('home_page_sections')->insert([
                'section_key' => $key,
                'enabled' => true,
                'sort_order' => $index + 1,
                'items_to_show' => in_array($key, ['hero', 'lookbook'], true) ? 5 : 8,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::create('home_testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name', 120);
            $table->text('review');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_testimonials');
        Schema::dropIfExists('home_page_sections');
        Schema::table('home_carousel_slides', function (Blueprint $table) {
            $table->dropIndex(['section_key']);
            $table->dropColumn('section_key');
        });
    }
};
