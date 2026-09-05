<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Throwable;

class Setting extends Model
{
    use HasFactory;

    public const DEFAULT_LOGO = 'assets/images/logo.png';

    public const DEFAULT_FAVICON = 'assets/images/favicon-round.png';

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /**
     * Get setting value by key with optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Set setting value by key.
     */
    public static function set(string $key, mixed $value, string $group = 'general'): static
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value, 'group' => $group]
        );
    }

    /**
     * Return every setting as a key/value array with a single query.
     */
    public static function values(): array
    {
        return static::query()->pluck('value', 'key')->all();
    }

    public static function logoUrl(?array $settings = null): string
    {
        return static::storedAssetUrl('site_logo', self::DEFAULT_LOGO, $settings);
    }

    public static function faviconUrl(?array $settings = null): string
    {
        return static::storedAssetUrl('site_favicon', self::DEFAULT_FAVICON, $settings);
    }

    public static function logoPath(?array $settings = null): string
    {
        $storedPath = $settings === null ? static::get('site_logo') : ($settings['site_logo'] ?? null);

        if ($storedPath && Storage::disk('public')->exists($storedPath)) {
            return Storage::disk('public')->path($storedPath);
        }

        return public_path(self::DEFAULT_LOGO);
    }

    public static function decryptSecret(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (Throwable) {
            // Backwards compatibility if a value was stored before encryption was added.
            return $value;
        }
    }

    protected static function storedAssetUrl(string $key, string $default, ?array $settings = null): string
    {
        $storedPath = $settings === null ? static::get($key) : ($settings[$key] ?? null);

        if ($storedPath && Storage::disk('public')->exists($storedPath)) {
            return asset('media/' . ltrim(str_replace(['storage/', 'media/'], '', $storedPath), '/'));
        }

        return asset($default);
    }
}
