<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'house_building',
        'street',
        'area',
        'city',
        'district',
        'state',
        'pin_code',
        'subtotal',
        'discount',
        'shipping',
        'grand_total',
        'payment_method',
        'payment_status',
        'order_status',
        'is_cancellation_disabled',
        'is_dispatched_to_courier',
        'courier_partner',
        'tracking_number',
        'dispatched_at',
        'order_source',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'is_cancellation_disabled' => 'boolean',
        'is_dispatched_to_courier' => 'boolean',
        'dispatched_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function getFullAddressAttribute(): string
    {
        return "{$this->house_building}, {$this->street}, {$this->area}, {$this->city}, {$this->district}, {$this->state} - {$this->pin_code}";
    }

    public static function generateOrderNumber(): string
    {
        $dateStr = now()->format('Ymd');
        $prefix = "QW-{$dateStr}-";
        $lastOrder = self::where('order_number', 'LIKE', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNum = (int) substr($lastOrder->order_number, -5);
            $nextNum = str_pad($lastNum + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '00001';
        }

        return $prefix . $nextNum;
    }
}
