<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'background_image',
        'text_color',
        'status',
        'sort_order',
        'is_combo_offer',
        'min_count',
        'combo_price',
        'delivery_charge',
    ];

    protected $casts = [
        'is_combo_offer' => 'boolean',
        'min_count' => 'integer',
        'combo_price' => 'float',
        'delivery_charge' => 'float',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'category_product')->withTimestamps();
    }

    public function comboProducts()
    {
        return $this->hasMany(Product::class, 'combo_category_id');
    }

    public function getUnitOfferPriceAttribute(): float
    {
        if ($this->is_combo_offer && $this->min_count > 0 && $this->combo_price > 0) {
            return round($this->combo_price / $this->min_count, 2);
        }
        return 0.0;
    }


    public function activeProducts()
    {
        return $this->belongsToMany(Product::class, 'category_product')->where('products.status', 'active')->withTimestamps();
    }

    public function getBackgroundImageUrlAttribute(): string
    {
        if (!$this->background_image) {
            return Setting::logoUrl();
        }

        if (str_starts_with($this->background_image, 'http://') || str_starts_with($this->background_image, 'https://') || filter_var($this->background_image, FILTER_VALIDATE_URL)) {
            return $this->background_image;
        }

        $cleanPath = ltrim(str_replace(['storage/', 'media/'], '', $this->background_image), '/');
        return asset('media/' . $cleanPath);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }
}
