<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `products` MODIFY COLUMN `discount_type` VARCHAR(20) NOT NULL DEFAULT 'none'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `products` MODIFY COLUMN `discount_type` ENUM('none', 'fixed', 'percentage') NOT NULL DEFAULT 'none'");
    }
};
