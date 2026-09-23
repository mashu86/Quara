<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Insert the prepared, encrypted Gemini API keys for live deployments.
     *
     * These values are already Laravel-encrypted ciphertexts, so they must not
     * be encrypted a second time here.
     */
    public function up(): void
    {
        if (! Schema::hasTable('gemini_api_keys')) {
            return;
        }

        $keys = [
            [
                'id' => 1,
                'name' => 'Gemini Key 1 (Default)',
                'api_key' => 'eyJpdiI6InJMMk1oWWtySWUrYmVseFowdkR1eUE9PSIsInZhbHVlIjoiSlB0R2dRUkZrdXMydkVkVUR6UXB6ajhhalZRcDBWeGNYQTNLYnlRM3ZKWjJiQUNxZVVnQ3l0QnhYUTFxWmpWVmFCeXI4U2I0ZVJnelRBcm1TY25Jb1E9PSIsIm1hYyI6ImI2NDI3OTdiMTk0OGY3ZWFkZmMzYjdjNTZmMmYxNDc3M2VmMTM0NjM3MGFiYTRhMjJiN2NmMWJiODZiYWRkOTgiLCJ0YWciOiIifQ==',
                'is_active' => false,
                'last_used_at' => null,
                'created_at' => '2026-09-23 16:10:14',
                'updated_at' => '2026-09-23 16:19:40',
            ],
            [
                'id' => 2,
                'name' => 'GEMINI KEY 2',
                'api_key' => 'eyJpdiI6IkkzTVlVeTBlSklVYWFaN0lmK2pyaUE9PSIsInZhbHVlIjoiL2p0NUFsdjU5cHpseFk2cHhMOTVBQUF4akdDcVR0Q2ZZeWcvN0xjekViaFhhVjU3b1RrQnVYQlByU2MzWHB2dlJiemhtSVJCSnJmYzZCMThnN1ZVVmc9PSIsIm1hYyI6ImVhN2E0YjJhOTg5MjIyMzY2NGUyMTM4OTVmNzZiOGNlZGE5MjRmNzIxOTNjNDMyNzVmMjZkOWRiMzE5OGJlYzEiLCJ0YWciOiIifQ==',
                'is_active' => true,
                'last_used_at' => null,
                'created_at' => '2026-09-23 16:11:36',
                'updated_at' => '2026-09-23 16:19:40',
            ],
            [
                'id' => 3,
                'name' => 'key 3',
                'api_key' => 'eyJpdiI6IkpGK0dVbFpGeUJvazUwendHbWl6b1E9PSIsInZhbHVlIjoieHhGNUNLOFFCK0JXNTlZWGg1K3h2dDEyWTlEYmVUdldZQng5c3JRaXZiTy9UbzIrR0piTmpEUFhQTG43a0JMTkZtUjNTejAwWk4xSHRWN204VFNFRVE9PSIsIm1hYyI6ImZhY2IyYWYwMDQ0MWEyYmQyZjllYjVhZDZmOTg4NGE3ZTgyZTBlZGVlMDdjZWQ4NTVkYTA4ZmQ3NWRiZTljYjMiLCJ0YWciOiIifQ==',
                'is_active' => false,
                'last_used_at' => null,
                'created_at' => '2026-09-23 16:18:11',
                'updated_at' => '2026-09-23 16:19:40',
            ],
            [
                'id' => 4,
                'name' => 'key 4',
                'api_key' => 'eyJpdiI6InNNWWk1ZkpRNG9VVGdwZkdQVTBBWHc9PSIsInZhbHVlIjoicWNLdGtseFNCdmF3U3FQV21FdW00U0pmV1JnZnBwT3NQaUdCNE1ncUFuUWhyZHYzVlprWmZJb2FXQUVQd0tXb3pCeWpQUjUyN0t5Sk15TGlwUDRXUXc9PSIsIm1hYyI6IjM3MWQzYzA0MGJmYWY4NjM0NjI5ZWZlNTNkOTAyNGU5MTJkMzVhYWZiNDlhZWM5NWI3NmU2NTRmNTZkNDVmNjgiLCJ0YWciOiIifQ==',
                'is_active' => false,
                'last_used_at' => null,
                'created_at' => '2026-09-23 16:19:31',
                'updated_at' => '2026-09-23 16:19:40',
            ],
        ];

        DB::transaction(function () use ($keys): void {
            foreach ($keys as $key) {
                DB::table('gemini_api_keys')->updateOrInsert(
                    ['id' => $key['id']],
                    $key
                );
            }

            DB::table('gemini_api_keys')
                ->where('id', '!=', 2)
                ->update(['is_active' => false]);

            if (Schema::hasTable('settings')) {
                $now = now();
                DB::table('settings')->updateOrInsert(
                    ['key' => 'gemini_api_key'],
                    [
                        'value' => $keys[1]['api_key'],
                        'group' => 'ai',
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        });
    }

    public function down(): void
    {
        // Keep credentials in place when rolling back unrelated migrations.
    }
};
