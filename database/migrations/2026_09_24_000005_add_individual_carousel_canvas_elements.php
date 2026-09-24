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
            $table->decimal('heading_x', 5, 2)->default(3);
            $table->decimal('heading_y', 5, 2)->default(60);
            $table->decimal('subheading_x', 5, 2)->default(3);
            $table->decimal('subheading_y', 5, 2)->default(74);
            $table->decimal('button_x', 5, 2)->default(3);
            $table->decimal('button_y', 5, 2)->default(88);
            $table->json('overlay_items')->nullable();
        });

        DB::table('home_carousel_slides')->update([
            'heading_x' => DB::raw('text_x'),
            'heading_y' => DB::raw('text_y'),
            'subheading_x' => DB::raw('text_x'),
            'subheading_y' => DB::raw('LEAST(92, text_y + 14)'),
            'button_x' => DB::raw('text_x'),
            'button_y' => DB::raw('LEAST(92, text_y + 28)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('home_carousel_slides', function (Blueprint $table) {
            $table->dropColumn(['heading_x', 'heading_y', 'subheading_x', 'subheading_y', 'button_x', 'button_y', 'overlay_items']);
        });
    }
};
