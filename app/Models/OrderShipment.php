<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderShipment extends Model
{
    protected $fillable = ['order_id','article_number','tracking_number','courier_name','receiver_name','receiver_address','weight','weight_unit','courier_charge','shipment_date','shipment_status','created_by','updated_by'];
    protected $casts = ['weight' => 'decimal:2', 'courier_charge' => 'decimal:2', 'shipment_date' => 'date'];
    public function order() { return $this->belongsTo(Order::class); }
}
