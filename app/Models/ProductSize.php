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
        'reserved_stock',
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

    public function getAvailableStockAttribute(): int
    {
        return $this->availableStockForOrder();
    }

    public function availableStockForOrder(?int $excludeOrderId = null): int
    {
        $query = OrderItem::query()->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.product_id', $this->product_id)
            ->where('order_items.size', $this->size)
            ->where('orders.payment_method', 'online')
            ->where('orders.payment_status', 'pending')
            ->where('orders.order_status', 'pending')
            ->where('orders.reserved_until', '>', now());
        if ($excludeOrderId !== null) {
            $query->where('orders.id', '!=', $excludeOrderId);
        }
        // Use a current read after waiting for a stock lock (including MySQL REPEATABLE READ).
        if (\Illuminate\Support\Facades\DB::transactionLevel() > 0) {
            $query->lockForUpdate();
        }
        $held = $query->get(['order_items.quantity'])->sum('quantity');

        return max(0, (int) $this->stock - (int) ($this->attributes['reserved_stock'] ?? 0) - $held);
    }
}
