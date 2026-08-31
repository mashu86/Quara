<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'price',
        'discount_type',
        'discount_value',
        'final_price',
        'description',
        'status',
        'is_out_of_stock',
        'delivery_charge_type',
        'weight_kg',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'final_price' => 'decimal:2',
        'weight_kg' => 'decimal:2',
        'is_out_of_stock' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product')->withTimestamps();
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('is_primary', 'desc')->orderBy('sort_order', 'asc');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(ProductSize::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function orderOperations(): HasMany
    {
        return $this->hasMany(OrderOperation::class);
    }

    public function getTotalStockAttribute(): int
    {
        if ($this->is_out_of_stock) {
            return 0;
        }
        return (int) $this->sizes->sum('stock');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereHas('category', function ($catQ) {
                    $catQ->where('status', 'active');
                })->orWhereHas('categories', function ($catQ) {
                    $catQ->where('status', 'active');
                });
            });
    }

    public function scopeInStockFirst($query)
    {
        return $query->orderByRaw("
            CASE 
                WHEN is_out_of_stock = 1 THEN 1 
                WHEN (SELECT COALESCE(SUM(stock), 0) FROM product_sizes WHERE product_sizes.product_id = products.id) <= 0 THEN 1 
                ELSE 0 
            END ASC
        ");
    }

    public function getPrimaryImageUrlAttribute(): string
    {
        $primary = $this->images->first();
        if (!$primary || empty($primary->image_path)) {
            return Setting::logoUrl();
        }

        $path = $primary->image_path;

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $cleanPath = ltrim(str_replace('storage/', '', $path), '/');
        return route('media.show', ['path' => $cleanPath]);
    }

    public static function calculateFinalPrice($price, $discountType, $discountValue): float
    {
        $price = (float) $price;
        $discountValue = (float) $discountValue;

        if ($discountType === 'fixed') {
            $final = $price - $discountValue;
        } elseif ($discountType === 'percentage') {
            $final = $price - ($price * ($discountValue / 100));
        } else {
            $final = $price;
        }

        return max(0, round($final, 2));
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $baseSlug = Str::slug($product->name);
                $product->slug = $baseSlug . '-' . Str::random(4);
            }
            $product->final_price = self::calculateFinalPrice($product->price, $product->discount_type, $product->discount_value);
        });

        static::updating(function ($product) {
            $product->final_price = self::calculateFinalPrice($product->price, $product->discount_type, $product->discount_value);
        });
    }
}
