<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourierAddress extends Model
{
    protected $fillable = [
        'customer_name',
        'customer_phone',
        'house_building',
        'street',
        'city',
        'district',
        'state',
        'pin_code',
    ];
}
