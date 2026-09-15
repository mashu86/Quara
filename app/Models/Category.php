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
        'is_offer_category',
        'offer_type',
        'is_combo_offer',
        'min_count',
        'combo_price',
        'discount_value',
        'discount_type',
        'is_active_offer',
        'delivery_charge',
    ];

    protected $casts = [
        'is_offer_category' => 'boolean',
        'is_combo_offer' => 'boolean',
        'is_active_offer' => 'boolean',
        'min_count' => 'integer',
        'combo_price' => 'float',
        'discount_value' => 'float',
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

    public function getIsComboOfferAttribute(): bool
    {
        return (bool) ($this->attributes['is_combo_offer'] ?? false) || 
               (($this->attributes['is_offer_category'] ?? false) && ($this->attributes['offer_type'] ?? '') === 'combo');
    }

    public function getIsDiscountOfferAttribute(): bool
    {
        return (bool) (($this->attributes['is_offer_category'] ?? false) && ($this->attributes['offer_type'] ?? '') === 'discount');
    }

    public static function getActiveOfferCategory(): ?Category
    {
        return static::where('is_offer_category', true)->where('is_active_offer', true)->first();
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
