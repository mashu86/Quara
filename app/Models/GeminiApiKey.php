<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class GeminiApiKey extends Model
{
    use HasFactory;

    protected $table = 'gemini_api_keys';

    protected $fillable = [
        'name',
        'api_key',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    /**
     * Accessor for Decrypted Key
     */
    public function getDecryptedKeyAttribute(): ?string
    {
        if (empty($this->api_key)) {
            return null;
        }

        try {
            return Crypt::decryptString($this->api_key);
        } catch (\Throwable $e) {
            return Setting::decryptSecret($this->api_key) ?? $this->api_key;
        }
    }

    /**
     * Accessor for Masked Key
     */
    public function getMaskedKeyAttribute(): string
    {
        $raw = $this->decrypted_key;
        if (empty($raw)) {
            return '—';
        }

        $length = strlen($raw);
        if ($length <= 12) {
            return substr($raw, 0, 4) . '****';
        }

        return substr($raw, 0, 8) . '...' . substr($raw, -6);
    }

    /**
     * Set this key as the active key and deactivate all others
     */
    public function setActive(): void
    {
        static::query()->where('id', '!=', $this->id)->update(['is_active' => false]);
        $this->update(['is_active' => true]);

        // Sync with Setting table so legacy code references stay synchronized
        Setting::set('gemini_api_key', $this->api_key, 'ai');
    }
}
