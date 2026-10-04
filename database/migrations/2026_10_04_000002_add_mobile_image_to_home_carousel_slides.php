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
            $table->string('mobile_image_mime', 100)->nullable()->after('image_blob');
        });

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('ALTER TABLE home_carousel_slides ADD COLUMN mobile_image_blob BLOB NULL');
        } else {
            DB::statement('ALTER TABLE home_carousel_slides ADD mobile_image_blob LONGBLOB NULL AFTER mobile_image_mime');
        }
    }

    public function down(): void
    {
        Schema::table('home_carousel_slides', function (Blueprint $table) {
            $table->dropColumn(['mobile_image_mime', 'mobile_image_blob']);
        });
    }
};
