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
        return max(0, (int) $this->stock - (int) ($this->attributes['reserved_stock'] ?? 0));
    }
}
