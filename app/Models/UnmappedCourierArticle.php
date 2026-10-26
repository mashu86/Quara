<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnmappedCourierArticle extends Model
{
    public const NOT_MAPPED = 'Not mapped from order';
    public const CONNECTED = 'Connected';

    protected $fillable = ['article_number','tracking_number','receiver_name','receiver_address','pin_code','booking_date','receiver_phone','courier_charge','status','order_shipment_id','created_by','connected_by','connected_at'];
    protected $casts = ['courier_charge' => 'decimal:2', 'connected_at' => 'datetime'];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(OrderShipment::class, 'order_shipment_id');
    }
}
