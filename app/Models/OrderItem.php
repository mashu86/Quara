<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_size_id',
        'product_booking_id',
        'product_name',
        'size',
        'unit_price',
        'discount_amount',
        'final_unit_price',
        'quantity',
        'subtotal',
        'item_status',
        'inventory_condition',
        'return_date',
        'refund_date',
        'refund_amount',
        'exchange_item_id',
        'is_combo_offer',
        'combo_unit_price',
        'original_price',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'combo_unit_price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'is_combo_offer' => 'boolean',
        'return_date' => 'date',
        'refund_date' => 'date',
    ];

    public function refunds(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderRefund::class, 'order_item_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productSize(): BelongsTo
    {
        return $this->belongsTo(ProductSize::class);
    }

    public function productBooking(): BelongsTo
    {
        return $this->belongsTo(ProductBooking::class);
    }

    public function getReturnedQuantityAttribute(): int
    {
        return (int) $this->hasMany(OrderOperation::class, 'order_item_id')
            ->where('status', 'active')->where('operation_type', 'product_returned')->sum('quantity');
    }

    public function getReturnableQuantityAttribute(): int
    {
        return max(0, (int) $this->quantity - $this->returned_quantity);
    }
}
