<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductSize extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'size',
        'stock',
        'chest',
        'waist',
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

    public function getReservedStockAttribute(): int
    {
        return (int) OrderItem::where('product_size_id', $this->id)
            ->whereHas('order', function ($q) {
                $q->where('order_status', 'pending')
                  ->where('payment_status', 'pending')
                  ->where('payment_method', 'online')
                  ->where('reserved_until', '>', now());
            })
            ->sum('quantity');
    }

    public function getAvailableStockAttribute(): int
    {
        return max(0, (int) $this->stock - $this->reserved_stock);
    }
}
