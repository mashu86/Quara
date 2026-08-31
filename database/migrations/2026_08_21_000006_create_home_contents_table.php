<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_contents', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('Main Home Banner & Content');
            $table->longText('content_html')->nullable();
            $table->longText('custom_css')->nullable();
            $table->string('image_position')->default('top');
            $table->string('image_mime')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // Add binary BLOB column using raw DB statement for binary image storage support
        if (DB::getDriverName() === 'sqlite') {
            DB::statement("ALTER TABLE home_contents ADD COLUMN image_blob BLOB NULL");
        } else {
            DB::statement("ALTER TABLE home_contents ADD image_blob LONGBLOB NULL AFTER image_mime");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('home_contents');
    }
};
