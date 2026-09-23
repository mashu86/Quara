<?php

use App\Models\GeminiApiKey;
use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Import the local Gemini-key list into live during `php artisan migrate`.
     *
     * The raw values must be supplied by GEMINI_API_KEYS_JSON on the target
     * server. They are encrypted here using the target server's APP_KEY.
     */
    public function up(): void
    {
        if (! Schema::hasTable('gemini_api_keys')) {
            return;
        }

        $keys = config('services.gemini.seed_keys', []);

        if (! is_array($keys) || $keys === []) {
            return;
        }

        DB::transaction(function () use ($keys): void {
            $activeKeyId = null;
            $firstImportedKeyId = null;

            foreach ($keys as $index => $key) {
                $name = trim((string) ($key['name'] ?? ''));
                $apiKey = trim((string) ($key['api_key'] ?? ''));

                if ($name === '' || $apiKey === '') {
                    throw new RuntimeException("Gemini key entry #" . ($index + 1) . ' must include name and api_key.');
                }

                $geminiKey = GeminiApiKey::query()->updateOrCreate(
                    ['name' => $name],
                    [
                        'api_key' => Crypt::encryptString($apiKey),
                        'is_active' => false,
                    ]
                );

                $firstImportedKeyId ??= $geminiKey->id;

                if ($activeKeyId === null && filter_var($key['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                    $activeKeyId = $geminiKey->id;
                }
            }

            $activeKeyId ??= $firstImportedKeyId;

            GeminiApiKey::query()->where('id', '!=', $activeKeyId)->update(['is_active' => false]);
            $activeKey = GeminiApiKey::query()->findOrFail($activeKeyId);
            $activeKey->update(['is_active' => true]);
            Setting::set('gemini_api_key', $activeKey->api_key, 'ai');
        });
    }

    public function down(): void
    {
        // Imported API keys are credentials and must not be deleted automatically.
    }
};
