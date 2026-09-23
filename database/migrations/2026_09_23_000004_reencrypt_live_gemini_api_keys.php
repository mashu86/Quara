<?php

use App\Models\GeminiApiKey;
use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repair keys previously inserted as ciphertext from another APP_KEY.
     * Plain API keys must be supplied through GEMINI_API_KEYS_JSON so they
     * can be encrypted using this deployment's APP_KEY.
     */
    public function up(): void
    {
        if (! Schema::hasTable('gemini_api_keys')) {
            return;
        }

        $keys = config('services.gemini.seed_keys', []);

        if (! is_array($keys) || $keys === []) {
            Log::warning('Gemini key re-encryption skipped: GEMINI_API_KEYS_JSON is empty. Re-enter the API keys in Admin > Gemini API Keys or configure GEMINI_API_KEYS_JSON, then run the Gemini key seeder.');

            return;
        }

        DB::transaction(function () use ($keys): void {
            $activeKeyId = null;
            $firstImportedKeyId = null;

            foreach ($keys as $index => $key) {
                $name = trim((string) ($key['name'] ?? ''));
                $apiKey = trim((string) ($key['api_key'] ?? ''));

                if ($name === '' || $apiKey === '') {
                    throw new \RuntimeException('Gemini key entry #'.($index + 1).' must include name and api_key.');
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
        // Credentials are retained when rolling back unrelated migrations.
    }
};
