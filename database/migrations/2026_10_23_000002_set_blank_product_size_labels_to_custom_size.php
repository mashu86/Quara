<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('product_sizes')
            ->where(function ($query) {
                $query->whereNull('size')->orWhereRaw("TRIM(size) = ''");
            })
            ->update(['size' => 'Custom Size']);
    }

    public function down(): void
    {
        // Keep the assigned labels: existing "Custom Size" values cannot be
        // distinguished safely from labels written before this migration.
    }
};
