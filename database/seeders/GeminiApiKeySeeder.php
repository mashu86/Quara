<?php

namespace Database\Seeders;

use App\Models\GeminiApiKey;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GeminiApiKeySeeder extends Seeder
{
    /**
     * Import Gemini keys from GEMINI_API_KEYS_JSON on the target server.
     *
     * API keys are deliberately supplied through the live environment, then
     * encrypted with the live APP_KEY before being stored in the database.
     */
    public function run(): void
    {
        $keys = config('services.gemini.seed_keys', []);

        if (! is_array($keys) || $keys === []) {
            $this->command?->warn('No Gemini API keys were imported: GEMINI_API_KEYS_JSON is empty.');

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

            // Keep the exact one-active-key rule even if the JSON omits it.
            $activeKeyId ??= $firstImportedKeyId;

            if ($activeKeyId !== null) {
                GeminiApiKey::query()->where('id', '!=', $activeKeyId)->update(['is_active' => false]);
                GeminiApiKey::query()->findOrFail($activeKeyId)->setActive();
            }
        });

        $this->command?->info('Gemini API keys imported and encrypted with this server\'s APP_KEY.');
    }
}
