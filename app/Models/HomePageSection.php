<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomePageSection extends Model
{
    protected $fillable = ['section_key', 'enabled', 'sort_order', 'items_to_show'];

    protected $casts = ['enabled' => 'boolean'];

    public const TITLES = [
        'hero' => 'Hero',
        'categories' => 'Categories',
        'new_arrivals' => 'New Arrivals',
        'best_sellers' => 'Best Sellers',
        'offers' => 'Offers',
        'lookbook' => 'Lookbook',
        'reviews' => 'Instagram / Reviews',
    ];

    public static function syncDefaults(): void
    {
        foreach (array_keys(self::TITLES) as $index => $key) {
            static::firstOrCreate(['section_key' => $key], [
                'enabled' => true,
                'sort_order' => $index + 1,
                'items_to_show' => in_array($key, ['hero', 'lookbook'], true) ? 5 : 8,
            ]);
        }
    }
}
