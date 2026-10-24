<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'measurement_type',
        'collection_visible',
        'name',
        'product_code',
        'slug',
        'price',
        'discount_type',
        'discount_value',
        'final_price',
        'description',
        'status',
        'is_out_of_stock',
        'booked_by',
        'booking_type',
        'booking_date',
        'delivery_charge_type',
        'weight_kg',
        'sort_order',
        'combo_category_id',
        'combo_sort_order',
        'display_size_chart',
        'size_master_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'final_price' => 'decimal:2',
        'weight_kg' => 'decimal:2',
        'is_out_of_stock' => 'boolean',
        'collection_visible' => 'boolean',
        'display_size_chart' => 'boolean',
        'combo_sort_order' => 'integer',
        'size_master_id' => 'integer',
        'booking_date' => 'date',
    ];

    public function sizeMaster(): BelongsTo
    {
        return $this->belongsTo(SizeMaster::class, 'size_master_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function comboCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'combo_category_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product')->withTimestamps();
    }

    public function scopeVisibleInCollection(Builder $query): Builder
    {
        $query->where('collection_visible', true)
            ->whereDoesntHave('category', fn (Builder $category) => $category->where('show_in_collection', false))
            ->whereDoesntHave('categories', fn (Builder $category) => $category->where('show_in_collection', false));

        static $hasComboCategoryId;
        $hasComboCategoryId ??= Schema::hasColumn('products', 'combo_category_id');
        if ($hasComboCategoryId) {
            $query->whereDoesntHave('comboCategory', fn (Builder $category) => $category->where('show_in_collection', false));
        }

        return $query;
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
    /**
     * Next free code in the PR1, PR2, ... series (highest PR number + 1). Manual codes outside the series are ignored.
     */
    public static function nextProductCode(): string
    {
        $max = static::query()->where('product_code', 'REGEXP', '^PR[0-9]+$')
            ->selectRaw('MAX(CAST(SUBSTRING(product_code, 3) AS UNSIGNED)) as max_code')->value('max_code');

        return 'PR' . ((int) $max + 1);
    }


    public function bookings(): HasMany
    {
        return $this->hasMany(ProductBooking::class);
    }

    /** Rows with a blank size label may hold measurements only, not a selectable variant. */
    public function selectableSizes(): HasMany
    {
        return $this->hasMany(ProductSize::class)
            ->whereNotNull('size')
            ->whereRaw("TRIM(size) <> ''");
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function orderOperations(): HasMany
    {
        return $this->hasMany(OrderOperation::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getTotalStockAttribute(): int
    {
        if ($this->is_out_of_stock) {
            return 0;
        }
        return (int) $this->sizes->sum('stock');
    }

    /** Units that can actually be purchased after bookings and pending reservations. */
    public function getAvailableTotalStockAttribute(): int
    {
        if ($this->is_out_of_stock) {
            return 0;
        }

        return (int) $this->sizes->sum(fn (ProductSize $size) => $size->available_stock);
    }

    public function getEffectiveFinalPriceAttribute(): float
    {
        return $this->discountOfferCategory()?->offerPriceFor($this->price) ?? (float) $this->final_price;
    }

    /** Price to store: a discount offer's price while it is on, else the product's own discount. */
    public function resolveFinalPrice(): float
    {
        return $this->discountOfferCategory()?->offerPriceFor($this->price)
            ?? self::calculateFinalPrice($this->price, $this->discount_type, $this->discount_value);
    }

    /**
     * The discount offer category this product is assigned to, switched on or not.
     * Other active combo categories must not shadow its discount category.
     */
    private function discountOfferCategory(): ?Category
    {
        $assignedOfferId = $this->combo_category_id ?: $this->category_id;

        return $assignedOfferId
            ? Category::whereKey($assignedOfferId)
                ->where('is_offer_category', true)
                ->where('offer_type', 'discount')
                ->first()
            : null;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
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

        $cleanPath = ltrim(str_replace(['storage/', 'media/'], '', $path), '/');
        return asset('media/' . $cleanPath);
    }

    public static function calculateFinalPrice($price, $discountType, $discountValue): float
    {
        $price = (float) $price;
        $discountValue = (float) $discountValue;

        if ($discountType === 'fixed' || $discountType === 'flat') {
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
            $product->final_price = $product->resolveFinalPrice();
        });

        static::updating(function ($product) {
            $product->final_price = $product->resolveFinalPrice();
        });
    }
}
