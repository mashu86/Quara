<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_method',
        'transaction_id',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'status',
        'amount',
        'response_payload',
        'razorpay_fee_percent',
        'razorpay_gst_percent',
        'razorpay_base_fee',
        'razorpay_gst_fee',
        'razorpay_total_charge',
        'razorpay_net_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'razorpay_fee_percent' => 'decimal:2',
        'razorpay_gst_percent' => 'decimal:2',
        'razorpay_base_fee' => 'decimal:2',
        'razorpay_gst_fee' => 'decimal:2',
        'razorpay_total_charge' => 'decimal:2',
        'razorpay_net_amount' => 'decimal:2',
        'response_payload' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
