<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeCarouselSetting extends Model
{
    protected $fillable = [
        'enabled', 'carousel_type', 'animation', 'interval_ms', 'visible_count',
        'items_desktop', 'items_tablet', 'items_mobile', 'margin_px', 'smart_speed_ms',
        'loop', 'show_nav', 'show_dots', 'autoplay', 'pause_on_hover', 'center_mode',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean', 'loop' => 'boolean', 'show_nav' => 'boolean',
            'show_dots' => 'boolean', 'autoplay' => 'boolean', 'pause_on_hover' => 'boolean',
            'center_mode' => 'boolean',
        ];
    }

    public static function current(): self
    {
        $setting = static::firstOrCreate([], [
            'enabled' => false,
            'carousel_type' => 'hero',
            'animation' => 'slide',
            'interval_ms' => 5000,
            'visible_count' => 5,
        ]);

        // Older preview builds stored the animation name in this layout field.
        if (!in_array($setting->carousel_type, ['hero', 'contained'], true)) {
            $setting->update(['carousel_type' => 'hero']);
        }

        return $setting;
    }
}
