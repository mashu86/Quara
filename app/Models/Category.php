<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

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
        'delivery_charge_mode',
        'delivery_charge',
        'allow_pre_min_purchase',
        'pre_min_purchase_offer_price',
        'minimum_purchase_required',
        'minimum_purchase_count',
        'show_in_collection',
    ];

    protected $casts = [
        'is_offer_category' => 'boolean',
        'is_combo_offer' => 'boolean',
        'is_active_offer' => 'boolean',
        'min_count' => 'integer',
        'combo_price' => 'float',
        'discount_value' => 'float',
        'delivery_charge' => 'float',
        'show_in_collection' => 'boolean',
        'allow_pre_min_purchase' => 'boolean',
        'pre_min_purchase_offer_price' => 'boolean',
        'minimum_purchase_required' => 'boolean',
        'minimum_purchase_count' => 'integer',
    ];

    protected static function booted(): void
    {
        // Merged categories stay in storage for audit/history but are never selectable or visible.
        static::addGlobalScope('unmerged', fn (Builder $query) => $query->whereNull('categories.merged_into_category_id'));

        // Turning a discount offer on/off or changing its discount reprices its products.
        static::saved(function (Category $category) {
            if ($category->wasChanged(['is_active_offer', 'status', 'discount_value', 'discount_type', 'offer_type', 'is_offer_category'])) {
                $category->syncOfferProductPrices();
            }
        });
    }

    /** A discount offer gives its price only while it is switched on and the category is active. */
    public function isLiveDiscountOffer(): bool
    {
        return $this->is_discount_offer && $this->is_active_offer
            && $this->status === 'active' && (float) $this->discount_value > 0;
    }

    /** The selling price for a product of this offer category: discounted only while the offer is live. */
    public function offerPriceFor($price): float
    {
        return $this->isLiveDiscountOffer()
            ? Product::calculateFinalPrice($price, $this->discount_type ?? 'percentage', $this->discount_value)
            : (float) $price;
    }

    /** Store the current offer price on each product assigned to this discount offer. */
    public function syncOfferProductPrices(): void
    {
        $wasDiscountOffer = $this->getOriginal('is_offer_category') && $this->getOriginal('offer_type') === 'discount';
        if (! $this->is_discount_offer && ! $wasDiscountOffer) {
            return; // Combo offers price at cart level and never touch stored product prices.
        }
        Product::where('combo_category_id', $this->id)
            ->orWhere(fn ($q) => $q->whereNull('combo_category_id')->where('category_id', $this->id))
            ->each(function (Product $product) {
                $finalPrice = $this->offerPriceFor($product->price);
                if ((float) $product->final_price !== $finalPrice) {
                    $product->update(['final_price' => $finalPrice]);
                }
            });
    }

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

    public function scopePublicActive($query)
    {
        return $query->where('status', 'active')
            ->whereNotNull('categories.slug')
            ->whereRaw("TRIM(categories.slug) <> ''")
            ->where(function ($q) {
                $q->where('is_offer_category', false)
                  ->orWhere('is_active_offer', true);
            });
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
