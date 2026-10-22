<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductBooking extends Model
{
    protected $fillable = ['product_id', 'product_size_id', 'quantity', 'sold_quantity', 'booked_by', 'booking_type', 'booking_date', 'status', 'created_by'];

    protected $casts = ['booking_date' => 'date', 'quantity' => 'integer', 'sold_quantity' => 'integer'];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function productSize(): BelongsTo { return $this->belongsTo(ProductSize::class); }
    public function orderItems(): HasMany { return $this->hasMany(OrderItem::class, 'product_booking_id'); }

    public function getRemainingQuantityAttribute(): int
    {
        return max(0, (int) $this->quantity - (int) $this->sold_quantity);
    }
}
