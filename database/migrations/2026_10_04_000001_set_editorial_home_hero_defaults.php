<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('home_carousel_settings')) {
            DB::table('home_carousel_settings')->where('smart_speed_ms', 450)->update(['smart_speed_ms' => 600]);

            if (Schema::hasTable('home_carousel_slides') && DB::table('home_carousel_slides')
                ->where('section_key', 'hero')->where('status', 'active')->exists()) {
                DB::table('home_carousel_settings')->update(['enabled' => true]);
            }
        }

        if (Schema::hasTable('home_page_sections')) {
            $heroSection = DB::table('home_page_sections')->where('section_key', 'hero')->exists();
            if ($heroSection) {
                DB::table('home_page_sections')->where('section_key', 'hero')->update(['enabled' => true]);
            } else {
                DB::table('home_page_sections')->insert([
                    'section_key' => 'hero', 'enabled' => true, 'sort_order' => 1, 'items_to_show' => 5,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Keep the live carousel configuration when rolling back this defaults migration.
    }
};
