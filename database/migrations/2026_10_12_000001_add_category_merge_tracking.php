<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('merged_into_category_id')->nullable()->after('status')
                ->constrained('categories')->restrictOnDelete();
            $table->index('merged_into_category_id');
        });

        Schema::create('category_merge_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('new_category_id')->constrained('categories')->restrictOnDelete();
            $table->string('new_category_name');
            $table->json('old_categories');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_merge_reports');
        Schema::table('categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('merged_into_category_id');
        });
    }
};
