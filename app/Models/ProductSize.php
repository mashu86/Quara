<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductSize extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (self $productSize) {
            $size = trim((string) $productSize->size);
            $productSize->size = $size !== '' ? $size : 'Free Size';
        });
    }

    protected $fillable = [
        'product_id',
        'size',
        'stock',
        'reserved_stock',
        'chest',
        'waist',
        'hip',
        'length',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(ProductBooking::class);
    }

    public function getAvailableStockAttribute(): int
    {
        return $this->availableStockForOrder();
    }

    /** Unpaid online orders never hold stock: the first completed payment gets the piece. */
    public function availableStockForOrder(): int
    {
        $booked = ProductBooking::query()->where('product_size_id', $this->id)
            ->whereIn('status', ['active', 'partially_sold'])
            ->selectRaw('COALESCE(SUM(quantity - sold_quantity), 0) as remaining')->value('remaining');

        return max(0, (int) $this->stock - (int) ($this->attributes['reserved_stock'] ?? 0) - (int) $booked);
    }
}
