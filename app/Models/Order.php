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
        'wa_thank_you_count',
        'wa_couriered_count',
        'razorpay_fee_percent',
        'razorpay_gst_percent',
        'razorpay_base_fee',
        'razorpay_gst_fee',
        'razorpay_total_charge',
        'razorpay_net_amount',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'razorpay_fee_percent' => 'decimal:2',
        'razorpay_gst_percent' => 'decimal:2',
        'razorpay_base_fee' => 'decimal:2',
        'razorpay_gst_fee' => 'decimal:2',
        'razorpay_total_charge' => 'decimal:2',
        'razorpay_net_amount' => 'decimal:2',
        'is_cancellation_disabled' => 'boolean',
        'is_dispatched_to_courier' => 'boolean',
        'dispatched_at' => 'datetime',
    ];

    /**
     * Calculate and apply Razorpay payment gateway charges.
     */
    public function calculateRazorpayCharge(?float $paymentAmount = null, ?float $feePercent = null, ?float $gstPercent = null): array
    {
        $amount = $paymentAmount ?? (float) $this->grand_total;
        $feePct = $feePercent ?? (float) \App\Models\Setting::get('razorpay_fee_percent', 2.00);
        $gstPct = $gstPercent ?? (float) \App\Models\Setting::get('razorpay_gst_percent', 18.00);

        // Razorpay Base Fee = Amount * (Fee % / 100)
        $baseFee = round($amount * ($feePct / 100), 2);
        
        // GST on Razorpay Fee = Base Fee * (GST % / 100)
        $gstFee = round($baseFee * ($gstPct / 100), 2);

        // Total Razorpay Charge = Base Fee + GST Fee
        $totalCharge = round($baseFee + $gstFee, 2);

        // Net Amount Received = Amount - Total Charge
        $netAmount = round($amount - $totalCharge, 2);

        $data = [
            'razorpay_fee_percent' => $feePct,
            'razorpay_gst_percent' => $gstPct,
            'razorpay_base_fee' => $baseFee,
            'razorpay_gst_fee' => $gstFee,
            'razorpay_total_charge' => $totalCharge,
            'razorpay_net_amount' => $netAmount,
        ];

        $this->update($data);

        if ($this->payment) {
            $this->payment->update($data);
        }

        return $data;
    }

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
