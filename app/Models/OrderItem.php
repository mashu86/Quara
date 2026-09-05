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
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'refund_amount' => 'decimal:2',
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
}
