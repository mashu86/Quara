<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_carousel_settings', function (Blueprint $table) {
            $table->decimal('items_desktop', 3, 1)->default(1.5);
            $table->decimal('items_tablet', 3, 1)->default(1.2);
            $table->decimal('items_mobile', 3, 1)->default(1.05);
            $table->unsignedSmallInteger('margin_px')->default(14);
            $table->unsignedSmallInteger('smart_speed_ms')->default(450);
            $table->boolean('loop')->default(true);
            $table->boolean('show_nav')->default(true);
            $table->boolean('show_dots')->default(true);
            $table->boolean('autoplay')->default(true);
            $table->boolean('pause_on_hover')->default(true);
            $table->boolean('center_mode')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('home_carousel_settings', function (Blueprint $table) {
            $table->dropColumn([
                'items_desktop', 'items_tablet', 'items_mobile', 'margin_px', 'smart_speed_ms',
                'loop', 'show_nav', 'show_dots', 'autoplay', 'pause_on_hover', 'center_mode',
            ]);
        });
    }
};
