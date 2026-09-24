<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_carousel_slides', function (Blueprint $table) {
            $table->unsignedTinyInteger('heading_size')->default(40);
            $table->string('subheading_font', 40)->default('sans');
            $table->unsignedTinyInteger('subheading_size')->default(17);
            $table->string('button_text', 60)->nullable();
            $table->string('link_url', 2048)->nullable();
            $table->decimal('text_x', 5, 2)->default(3);
            $table->decimal('text_y', 5, 2)->default(66);
        });
    }

    public function down(): void
    {
        Schema::table('home_carousel_slides', function (Blueprint $table) {
            $table->dropColumn([
                'heading_size', 'subheading_font', 'subheading_size', 'button_text',
                'link_url', 'text_x', 'text_y',
            ]);
        });
    }
};
