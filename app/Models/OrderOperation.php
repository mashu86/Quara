<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderOperation extends Model
{
    use HasFactory;

    public const OPERATION_TYPES = [
        'product_returned' => 'Product Returned',
        'product_damaged' => 'Product Damaged',
        'customer_return' => 'Customer Return',
        'wrong_product_sent' => 'Wrong Product Sent',
        'product_lost' => 'Product Lost',
        'product_exchange' => 'Product Exchange',
        'dummy_test' => 'Dummy / Razorpay Test',
        'other' => 'Other',
    ];

    protected $fillable = [
        'order_id',
        'product_id',
        'order_item_id',
        'operation_type',
        'other_description',
        'status',
        'quantity',
        'is_product_restored',
        'inventory_condition',
        'return_date',
        'refund_date',
        'replacement_product_id',
        'replacement_product_size_id',
        'replacement_quantity',
        'price_difference',
        'is_money_refunded',
        'product_refund_amount',
        'delivery_refund_amount',
        'other_refund_amount',
        'total_refund_amount',
        'additional_expense_total',
        'total_financial_adjustment',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_product_restored' => 'boolean',
        'is_money_refunded' => 'boolean',
        'return_date' => 'date',
        'refund_date' => 'date',
        'product_refund_amount' => 'decimal:2',
        'delivery_refund_amount' => 'decimal:2',
        'other_refund_amount' => 'decimal:2',
        'total_refund_amount' => 'decimal:2',
        'additional_expense_total' => 'decimal:2',
        'total_financial_adjustment' => 'decimal:2',
        'price_difference' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function replacementProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'replacement_product_id');
    }

    public function replacementProductSize(): BelongsTo
    {
        return $this->belongsTo(ProductSize::class, 'replacement_product_size_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(OrderOperationExpense::class, 'order_operation_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(OrderRefund::class, 'order_operation_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function getOperationTypeLabelAttribute(): string
    {
        if ($this->operation_type === 'other' && $this->other_description) {
            return 'Other: ' . $this->other_description;
        }

        return self::OPERATION_TYPES[$this->operation_type] ?? ucfirst(str_replace('_', ' ', $this->operation_type));
    }

    public function recalculateTotals(): void
    {
        if ($this->is_money_refunded) {
            $this->total_refund_amount = (float) $this->product_refund_amount 
                + (float) $this->delivery_refund_amount 
                + (float) $this->other_refund_amount;
        } else {
            $this->total_refund_amount = 0.00;
        }

        $this->additional_expense_total = (float) $this->expenses()->sum('amount');
        $this->total_financial_adjustment = $this->total_refund_amount + $this->additional_expense_total;
        $this->saveQuietly();
    }
}
